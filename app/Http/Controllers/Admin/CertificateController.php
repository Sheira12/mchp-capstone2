<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Parishioner;
use App\Models\SacramentalRecord;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    public function index(Request $request)
    {
        $query = Certificate::with(['parishioner', 'issuedBy']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($verStatus = $request->get('ver_status')) {
            $query->where('record_verification_status', $verStatus);
        }

        if ($search = $request->get('search')) {
            // Wrap all OR conditions in a grouped where so type/status filters
            // are not bypassed. Only search columns that actually exist on the
            // certificates table — sponsor/priest data lives on sacramental_records.
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                  ->orWhereHas('parishioner', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('middle_name', 'like', "%{$search}%")
                         ->orWhere('contact_number', 'like', "%{$search}%")
                         ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", ["%{$search}%"])
                         ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) ILIKE ?", ["%{$search}%"]);
                  })
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  // Sponsor/priest/register data lives on sacramental_records
                  ->orWhereHas('sacramentalRecord', function ($sq) use ($search) {
                      $sq->where('celebrant', 'like', "%{$search}%")
                         ->orWhere('register_number', 'like', "%{$search}%")
                         ->orWhere('venue', 'like', "%{$search}%");
                  });
            });
        }

        $unverifiedCount = Certificate::whereIn('type', \App\Models\Certificate::REQUIRES_RECORD)
            ->whereIn('record_verification_status', ['pending', 'unverified'])
            ->count();

        $certificates = $query->orderByDesc('issued_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.certificates.index', compact('certificates', 'unverifiedCount'));
    }

    public function create(Request $request)
    {
        $parishioner = $request->get('parishioner_id')
            ? Parishioner::find($request->get('parishioner_id'))
            : null;

        $sacramentalRecord = $request->get('sacramental_record_id')
            ? SacramentalRecord::find($request->get('sacramental_record_id'))
            : null;

        return view('admin.certificates.create', compact('parishioner', 'sacramentalRecord'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parishioner_id'        => ['required', 'exists:parishioners,id'],
            'sacramental_record_id' => ['nullable', 'exists:sacramental_records,id'],
            'type'                  => ['required', 'string'],
            'issued_date'           => ['required', 'date'],
            'purpose'               => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
        ]);

        // Treat empty string as null for optional FK
        if (empty($validated['sacramental_record_id'])) {
            $validated['sacramental_record_id'] = null;
        }

        $validated['issued_by'] = auth()->id();

        // Wrap in a transaction so the number generation and INSERT are atomic
        $certificate = \DB::transaction(function () use ($validated) {
            return Certificate::create($validated);
        });

        // Generate PDF and QR code — increase time limit for this operation
        set_time_limit(120);
        try {
            $this->certificateService->generate($certificate);
            $certificate->refresh();
            $message = 'Certificate created and generated successfully.';
        } catch (\Exception $e) {
            \Log::error('Certificate generation failed: ' . $e->getMessage());
            $message = 'Certificate created. PDF generation failed — use Regenerate to retry.';
        }

        AuditLog::record('create', $certificate, [], $certificate->toArray(), 'Certificate created');

        // Log initial status
        \App\Models\CertificateStatusHistory::log($certificate, null, $certificate->status, 'Certificate created by admin');

        return redirect()->route('admin.certificates.show', $certificate)
            ->with('success', $message);
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['parishioner', 'sacramentalRecord', 'issuedBy', 'payment', 'qrCode']);
        return view('admin.certificates.show', compact('certificate'));
    }

    public function download(Certificate $certificate)
    {
        set_time_limit(120);
        if (!$certificate->file_path || !\Storage::disk('public')->exists($certificate->file_path)) {
            $this->certificateService->generate($certificate);
            $certificate->refresh();
        }

        AuditLog::record('download', $certificate, [], [], 'Certificate downloaded');

        return \Storage::disk('public')->download($certificate->file_path, $certificate->certificate_number . '.pdf');
    }

    public function regenerate(Certificate $certificate)
    {
        set_time_limit(120);
        try {
            $this->certificateService->generate($certificate);
            return back()->with('success', 'Certificate regenerated successfully.');
        } catch (\Exception $e) {
            \Log::error('Certificate regeneration failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Regeneration failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Manually mark a certificate's sacramental record as verified by staff.
     * Used when the auto-match fails but staff have manually confirmed the record.
     */
    public function verifyRecord(Certificate $certificate)
    {
        $certificate->update([
            'record_verification_status' => 'verified',
            'staff_notes'                => 'Manually verified by ' . auth()->user()->name . ' on ' . now()->format('M d, Y'),
        ]);

        AuditLog::record('verify_record', $certificate, ['record_verification_status' => 'pending'], ['record_verification_status' => 'verified'], 'Record manually verified by admin');

        \App\Models\CertificateStatusHistory::log($certificate, null, 'verified', 'Record manually verified by ' . auth()->user()->name);

        // Notify the parishioner
        $linkedUser = \App\Models\User::where('parishioner_id', $certificate->parishioner_id)->first();
        if ($linkedUser) {
            try {
                $linkedUser->notify(new \App\Notifications\ParishionerStatusNotification(
                    'Certificate Record Verified ✓',
                    'Your ' . $certificate->getTypeLabel() . ' record has been verified by the parish office. Once the certificate is generated, you can download it.',
                    route('parishioner.certificates.index'),
                    'document'
                ));
            } catch (\Exception $e) {
                \Log::warning('Certificate verify notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Certificate record marked as verified. Parishioner has been notified.');
    }

    public function release(Certificate $certificate)
    {
        $old = $certificate->status;

        $certificate->update([
            'status'      => 'released',
            'released_at' => now(),
            'handled_by'  => auth()->id(),
        ]);

        // Log the status transition with IP
        \App\Models\CertificateStatusHistory::log(
            $certificate, $old, 'released',
            'Released by ' . auth()->user()->name, request()->ip()
        );

        AuditLog::record('release', $certificate, ['status' => $old], ['status' => 'released'], 'Certificate released by ' . auth()->user()->name);

        // Notify the parishioner their certificate is ready to download
        $linkedUser = \App\Models\User::where('parishioner_id', $certificate->parishioner_id)->first();
        if ($linkedUser) {
            try {
                $linkedUser->notify(new \App\Notifications\ParishionerStatusNotification(
                    'Certificate Ready for Download 📄',
                    'Your ' . $certificate->getTypeLabel() . ' is now ready. You can download it from your portal.',
                    route('parishioner.certificates.index'),
                    'document'
                ));
            } catch (\Exception $e) {
                \Log::warning('Certificate release notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Certificate marked as released. Parishioner has been notified.');
    }

    public function batchPrint(Request $request)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['exists:certificates,id']])['ids'];
        $certificates = Certificate::whereIn('id', $ids)->with('parishioner')->get();

        return $this->certificateService->batchPdf($certificates);
    }

    public function edit(Certificate $certificate)
    {
        $certificate->load(['parishioner', 'sacramentalRecord']);
        return view('admin.certificates.edit', compact('certificate'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'type'                  => ['required', 'string'],
            'issued_date'           => ['required', 'date'],
            'purpose'               => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
            'status'                => ['required', 'in:draft,issued,released,revoked'],
            // Sacramental record fields (all optional)
            'rec_date_administered' => ['nullable', 'date'],
            'rec_celebrant'         => ['nullable', 'string', 'max:255'],
            'rec_venue'             => ['nullable', 'string', 'max:255'],
            'rec_notes'             => ['nullable', 'string', 'max:500'],
            'rec_register_number'   => ['nullable', 'string', 'max:100'],
            'rec_page_number'       => ['nullable', 'string', 'max:20'],
            'rec_line_number'       => ['nullable', 'string', 'max:20'],
            'rec_godparents'        => ['nullable', 'array'],
            'rec_godparents.*'      => ['nullable', 'string', 'max:255'],
            'rec_sponsors'          => ['nullable', 'array'],
            'rec_sponsors.*'        => ['nullable', 'string', 'max:255'],
            'rec_witnesses'         => ['nullable', 'array'],
            'rec_witnesses.*'       => ['nullable', 'string', 'max:255'],
        ]);

        $oldValues = $certificate->toArray();

        // ── Update the certificate itself ──────────────────────────────────
        $certificate->update([
            'type'        => $validated['type'],
            'issued_date' => $validated['issued_date'],
            'purpose'     => $validated['purpose'] ?? null,
            'notes'       => $validated['notes'] ?? null,
            'status'      => $validated['status'],
        ]);

        // ── Update the linked sacramental record (if present) ─────────────
        if ($certificate->sacramentalRecord) {
            $record = $certificate->sacramentalRecord;

            // Filter out empty strings from array fields so they don't pollute the JSON
            $godparents = array_values(array_filter($validated['rec_godparents'] ?? [], fn($v) => trim($v ?? '') !== ''));
            $sponsors   = array_values(array_filter($validated['rec_sponsors']   ?? [], fn($v) => trim($v ?? '') !== ''));
            $witnesses  = array_values(array_filter($validated['rec_witnesses']  ?? [], fn($v) => trim($v ?? '') !== ''));

            $record->update([
                'date_administered' => $validated['rec_date_administered'] ?? $record->date_administered,
                'celebrant'         => $validated['rec_celebrant'] ?? $record->celebrant,
                'venue'             => $validated['rec_venue']     ?? $record->venue,
                'notes'             => $validated['rec_notes']     ?? $record->notes,
                'register_number'   => $validated['rec_register_number'] ?? $record->register_number,
                'page_number'       => $validated['rec_page_number']     ?? $record->page_number,
                'line_number'       => $validated['rec_line_number']      ?? $record->line_number,
                'godparents'        => empty($godparents) ? $record->godparents : $godparents,
                'sponsors'          => empty($sponsors)   ? $record->sponsors   : $sponsors,
                'witnesses'         => empty($witnesses)  ? $record->witnesses  : $witnesses,
            ]);
        }

        AuditLog::record('update', $certificate, $oldValues, $certificate->fresh()->toArray(), 'Certificate updated by ' . auth()->user()->name);

        // ── Re-generate the PDF so it reflects updated sacramental data ────
        set_time_limit(120);
        try {
            $this->certificateService->generate($certificate);
            $message = 'Certificate updated and PDF regenerated.';
        } catch (\Exception $e) {
            \Log::error('Certificate re-generation after edit failed: ' . $e->getMessage());
            $message = 'Certificate updated. PDF re-generation failed — use Regenerate to retry.';
        }

        return redirect()->route('admin.certificates.show', $certificate)->with('success', $message);
    }

    public function destroy(Certificate $certificate)
    {
        if ($certificate->file_path) {
            \Storage::disk('public')->delete($certificate->file_path);
        }
        AuditLog::record('delete', $certificate, $certificate->toArray(), [], 'Certificate deleted');
        $certificate->delete();
        return redirect()->route('admin.certificates.index')->with('success', 'Certificate deleted.');
    }
}
