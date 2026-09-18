<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\SacramentalRecord;
use App\Notifications\BookingStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $parishioner = auth()->user()->parishioner;

        if (!$parishioner) {
            return view('parishioner.certificates.index', ['certificates' => collect()]);
        }

        $query = $parishioner->certificates()->with(['sacramentalRecord', 'qrCode']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $certificates = $query->orderByDesc('issued_date')->paginate(10)->withQueryString();

        return view('parishioner.certificates.index', compact('certificates'));
    }

    /**
     * Show the certificate request form.
     */
    public function create()
    {
        $parishioner = auth()->user()->parishioner;

        if (!$parishioner) {
            return redirect()->route('parishioner.profile')
                ->with('error', 'Please complete your parishioner profile before requesting a certificate.');
        }

        // Load sacramental records to pre-fill the form
        try {
            $sacramentalRecords = $parishioner->sacramentalRecords()->orderBy('date_administered')->get();
        } catch (\Exception $e) {
            \Log::error('Certificate create: failed to load sacramental records', ['error' => $e->getMessage()]);
            $sacramentalRecords = collect();
        }

        $certificateTypes = Certificate::TYPES;

        return view('parishioner.certificates.create', compact('parishioner', 'sacramentalRecords', 'certificateTypes'));
    }

    /**
     * Submit a certificate request (creates a draft certificate for admin review).
     * Automatically checks if a matching sacramental record exists for the parishioner.
     */
    public function store(Request $request)
    {
        $parishioner = auth()->user()->parishioner;

        if (!$parishioner) {
            return redirect()->route('parishioner.profile')
                ->with('error', 'Please complete your parishioner profile first.');
        }

        $validated = $request->validate([
            'type'                  => ['required', 'string', 'in:' . implode(',', array_keys(Certificate::TYPES))],
            'sacramental_record_id' => ['nullable', 'exists:sacramental_records,id'],
            'purpose'               => ['required', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string', 'max:500'],
        ]);

        // Verify the chosen sacramental record belongs to this parishioner
        if (!empty($validated['sacramental_record_id'])) {
            $record = SacramentalRecord::find($validated['sacramental_record_id']);
            if (!$record || $record->parishioner_id !== $parishioner->id) {
                return back()->withErrors(['sacramental_record_id' => 'Invalid sacramental record selected.']);
            }
        }

        // ── Rate limit: max 5 requests per parishioner per day ────────────
        $todayCount = Certificate::where('parishioner_id', $parishioner->id)
            ->whereDate('requested_at', today())
            ->count();
        if ($todayCount >= 5) {
            return back()->withErrors([
                'error' => 'You have reached the maximum of 5 certificate requests per day. Please try again tomorrow or contact the parish office.',
            ]);
        }

        // Check for duplicate pending/issued certificate of same type
        $existing = Certificate::where('parishioner_id', $parishioner->id)
            ->where('type', $validated['type'])
            ->whereIn('status', ['draft', 'issued'])
            ->first();

        if ($existing && !$request->boolean('confirm_duplicate')) {
            return back()->withInput()->with('duplicate_warning', $existing);
        }

        // ── Auto-match sacramental record ──────────────────────────────────
        // For sacrament-type certificates: try to find a matching record.
        // This determines the initial record_verification_status.
        $linkedRecordId       = $validated['sacramental_record_id'] ?? null;
        $verificationStatus   = 'pending';   // default
        $staffNotes           = null;

        if (in_array($validated['type'], Certificate::REQUIRES_RECORD)) {
            $sacramentType = Certificate::TYPE_TO_SACRAMENT[$validated['type']] ?? null;

            if ($sacramentType) {
                // 1. Exact match: parishioner_id + sacrament type
                $exactRecord = SacramentalRecord::where('parishioner_id', $parishioner->id)
                    ->where('type', $sacramentType)
                    ->whereNull('deleted_at')
                    ->latest('date_administered')
                    ->first();

                if ($exactRecord) {
                    // Record found — mark as verified immediately
                    $linkedRecordId     = $linkedRecordId ?? $exactRecord->id;
                    $verificationStatus = 'verified';
                    $staffNotes         = 'Auto-verified: matching ' . $exactRecord->getTypeLabel()
                        . ' record found (Reg. No. ' . ($exactRecord->register_number ?? 'N/A') . ').';
                } else {
                    // No record found — flag for manual staff review
                    $verificationStatus = 'unverified';
                    $staffNotes         = 'No matching ' . ($sacramentType)
                        . ' record found for this parishioner in the parish registry. '
                        . 'Your request has been forwarded to the parish office for manual verification.';
                }
            }
        } else {
            // Types like membership, no_impediment don't need a sacramental record
            $verificationStatus = 'verified';
        }

        $certificate = \DB::transaction(function () use ($validated, $parishioner, $linkedRecordId, $verificationStatus, $staffNotes) {
            $cert = Certificate::create([
                'parishioner_id'             => $parishioner->id,
                'sacramental_record_id'      => $linkedRecordId,
                'type'                       => $validated['type'],
                'issued_date'                => now()->toDateString(),
                'purpose'                    => $validated['purpose'],
                'notes'                      => $validated['notes'] ?? null,
                'status'                     => 'draft',
                'record_verification_status' => $verificationStatus,
                'staff_notes'                => $staffNotes,
                'requested_at'               => now(),
            ]);

            // Log initial status
            \App\Models\CertificateStatusHistory::log($cert, null, 'draft', 'Certificate request submitted by parishioner');

            return $cert;
        });

        // Notify ALL admin users (shows in admin notification bell)
        $adminUsers = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();        foreach ($adminUsers as $admin) {
            try {
                $admin->notify(new \App\Notifications\AdminCertificateNotification($certificate));
            } catch (\Exception $e) {
                \Log::warning('Admin certificate notification failed: ' . $e->getMessage());
            }
        }

        // Notify the parishioner
        $portalMessage = $verificationStatus === 'unverified'
            ? 'We could not find your ' . $certificate->getTypeLabel()
              . ' record in our parish registry. Your request has been forwarded to the parish office for manual verification.'
            : 'Your request for a ' . $certificate->getTypeLabel()
              . ' has been submitted. We will process it within 1–3 working days.';

        auth()->user()->notify(new \App\Notifications\ParishionerStatusNotification(
            'Certificate Request Received',
            $portalMessage,
            route('parishioner.certificates.index'),
            'document'
        ));

        return redirect()->route('parishioner.certificates.index')
            ->with('success', $verificationStatus === 'unverified'
                ? 'Your certificate request has been submitted. No matching record was found automatically — the parish office will verify your records manually within 1–3 working days.'
                : 'Certificate request submitted successfully. The parish office will process it within 1–3 working days.'
            );
    }

    /**
     * Cancel a certificate request — only allowed while status is draft.
     */
    public function cancelRequest(Certificate $certificate)
    {
        if ($certificate->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }
        if ($certificate->status !== 'draft') {
            return back()->with('error', 'This certificate request can no longer be cancelled — it has already been processed.');
        }

        \App\Models\CertificateStatusHistory::log($certificate, 'draft', 'cancelled', 'Cancelled by parishioner');

        if ($certificate->file_path) {
            \Storage::disk('public')->delete($certificate->file_path);
        }
        $certificate->delete();

        return redirect()->route('parishioner.certificates.index')
            ->with('success', 'Your certificate request has been cancelled.');
    }

    public function download(Certificate $certificate)
    {        // SECURITY: Ensure the certificate belongs to the authenticated parishioner
        if ($certificate->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403, 'This certificate does not belong to your account.');
        }

        // GATE: Status must be released (not just issued — release is the public availability step)
        if ($certificate->status !== 'released') {
            return back()->withErrors(['error' => 'This certificate is not yet available for download. The parish office must release it first.']);
        }

        // GATE: For sacrament-type certificates, record must be verified
        // This is enforced server-side — cannot be bypassed by hitting the URL directly
        if (!$certificate->isDownloadable()) {
            return back()->withErrors([
                'error' => 'Download is not available yet. '
                    . ($certificate->record_verification_status === 'unverified'
                        ? 'No matching parish record was found. Please contact the parish office.'
                        : 'Your certificate is pending verification by the parish office.'),
            ]);
        }

        set_time_limit(60);
        if (!$certificate->file_path || !Storage::disk('public')->exists($certificate->file_path)) {
            app(\App\Services\CertificateService::class)->generate($certificate);
            $certificate->refresh();
        }

        return Storage::disk('public')->download(
            $certificate->file_path,
            $certificate->certificate_number . '.pdf'
        );
    }
}
