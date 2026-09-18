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
    public function index()
    {
        $parishioner  = auth()->user()->parishioner;
        $certificates = $parishioner
            ? $parishioner->certificates()
                ->with(['sacramentalRecord', 'qrCode'])
                ->orderByDesc('issued_date')
                ->paginate(10)
            : collect();

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
            return Certificate::create([
                'parishioner_id'             => $parishioner->id,
                'sacramental_record_id'      => $linkedRecordId,
                'type'                       => $validated['type'],
                'issued_date'                => now()->toDateString(),
                'purpose'                    => $validated['purpose'],
                'notes'                      => $validated['notes'] ?? null,
                'status'                     => 'draft',
                'record_verification_status' => $verificationStatus,
                'staff_notes'                => $staffNotes,
            ]);
        });

        // Notify ALL admin users (shows in admin notification bell)
        $adminUsers = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();
        foreach ($adminUsers as $admin) {
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

    public function download(Certificate $certificate)
    {
        // SECURITY: Ensure the certificate belongs to the authenticated parishioner
        if ($certificate->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403, 'This certificate does not belong to your account.');
        }

        // GATE: Status must be issued or released
        if (!in_array($certificate->status, ['issued', 'released'])) {
            return back()->withErrors(['error' => 'This certificate is not yet ready for download.']);
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
