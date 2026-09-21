<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\CertificateEditRequest;
use App\Models\CertificateStatusHistory;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateEditRequestController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    /**
     * List all pending edit requests.
     */
    public function index(Request $request)
    {
        $query = CertificateEditRequest::with(['certificate.parishioner', 'submittedBy'])
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at');

        if ($status = $request->get('status', 'pending')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $editRequests = $query->paginate(20)->withQueryString();
        $pendingCount = CertificateEditRequest::where('status', 'pending')->count();

        return view('admin.certificates.edit-requests.index', compact('editRequests', 'pendingCount'));
    }

    /**
     * Approve a correction request.
     * Writes approved changes BOTH to sacramental_record (if linked) AND to
     * cert_overrides on the certificate itself.  cert_overrides is the
     * authoritative source for PDF generation — it survives Render redeployments
     * (ephemeral FS) and works even when no sacramental record is linked.
     */
    public function approve(Request $request, CertificateEditRequest $editRequest)
    {
        if ($editRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'staff_response' => ['nullable', 'string', 'max:500'],
        ]);

        \DB::transaction(function () use ($editRequest, $validated) {
            $certificate = $editRequest->certificate;
            $record      = $certificate->sacramentalRecord;
            $changes     = $editRequest->requested_changes;

            // ── 1. Build the cert_overrides array (merges with any existing overrides)
            $overrides = $certificate->cert_overrides ?? [];

            $simpleMap = [
                'date_administered' => 'date_administered',
                'celebrant'         => 'celebrant',
                'venue'             => 'venue',
                'register_number'   => 'register_number',
                'page_number'       => 'page_number',
                'line_number'       => 'line_number',
                'notes'             => 'notes',
                'spouse_name'       => 'spouse_name',
                'parents_names'     => 'parents_names',
            ];

            foreach ($simpleMap as $changesKey => $overrideKey) {
                if (isset($changes[$changesKey]) && trim($changes[$changesKey]) !== '') {
                    $overrides[$overrideKey] = $changes[$changesKey];
                }
            }

            // Handle ninong/ninang → stored as tagged godparents array
            if (isset($changes['ninong']) || isset($changes['ninang'])) {
                $ninong = array_values(array_filter($changes['ninong'] ?? []));
                $ninang = array_values(array_filter($changes['ninang'] ?? []));
                $tagged = array_merge(
                    array_map(fn($n) => 'NINONG:' . $n, $ninong),
                    array_map(fn($n) => 'NINANG:' . $n, $ninang)
                );
                if (!empty($tagged)) {
                    $overrides['godparents'] = $tagged;
                }
            }

            if (isset($changes['sponsors'])) {
                $sponsors = array_values(array_filter($changes['sponsors']));
                if (!empty($sponsors)) $overrides['sponsors'] = $sponsors;
            }

            if (isset($changes['witnesses'])) {
                $witnesses = array_values(array_filter($changes['witnesses']));
                if (!empty($witnesses)) $overrides['witnesses'] = $witnesses;
            }

            // ── 2. Persist overrides on the certificate row
            $certificate->update(['cert_overrides' => $overrides]);

            // ── 3. Also mirror to sacramental_record if one is linked
            //       (keeps the registry in sync for other purposes)
            if ($record) {
                $recUpdate = [];
                foreach ($simpleMap as $changesKey => $col) {
                    if (isset($changes[$changesKey]) && trim($changes[$changesKey]) !== '') {
                        $recUpdate[$col] = $changes[$changesKey];
                    }
                }
                if (isset($overrides['godparents'])) {
                    $recUpdate['godparents'] = $overrides['godparents'];
                }
                if (isset($overrides['sponsors']))  $recUpdate['sponsors']  = $overrides['sponsors'];
                if (isset($overrides['witnesses'])) $recUpdate['witnesses'] = $overrides['witnesses'];
                if (!empty($recUpdate)) {
                    $record->update($recUpdate);
                }
            }

            // ── 4. Mark the edit request as approved
            $editRequest->update([
                'status'         => 'approved',
                'reviewed_by'    => auth()->id(),
                'reviewed_at'    => now(),
                'staff_response' => $validated['staff_response'] ?? 'Approved by ' . auth()->user()->name,
            ]);

            AuditLog::record(
                'approve_edit_request', $certificate,
                ['edit_request_id' => $editRequest->id],
                $changes,
                'Edit request approved by ' . auth()->user()->name
            );
        });

        // ── 5. Re-generate PDF — now reads from cert_overrides first
        set_time_limit(120);
        $certificate = $editRequest->certificate->fresh(['parishioner', 'sacramentalRecord', 'issuedBy', 'qrCode']);
        try {
            $this->certificateService->generate($certificate);
        } catch (\Exception $e) {
            \Log::error('PDF re-generation after edit approval failed: ' . $e->getMessage());
        }

        // ── 6. Notify the parishioner
        $linkedUser = \App\Models\User::where('parishioner_id', $certificate->parishioner_id)->first();
        if ($linkedUser) {
            try {
                $linkedUser->notify(new \App\Notifications\ParishionerStatusNotification(
                    'Correction Request Approved ✓',
                    'Your correction request for the ' . $certificate->getTypeLabel()
                        . ' has been approved and the certificate has been updated.',
                    route('parishioner.certificates.index'),
                    'document'
                ));
            } catch (\Exception $e) {
                \Log::warning('Edit request approval notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Correction approved and certificate PDF regenerated.');
    }

    /**
     * Reject a correction request with a reason.
     */
    public function reject(Request $request, CertificateEditRequest $editRequest)
    {
        if ($editRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validate([
            'staff_response' => ['required', 'string', 'max:500'],
        ]);

        $editRequest->update([
            'status'         => 'rejected',
            'reviewed_by'    => auth()->id(),
            'reviewed_at'    => now(),
            'staff_response' => $validated['staff_response'],
        ]);

        AuditLog::record('reject_edit_request', $editRequest->certificate,
            ['edit_request_id' => $editRequest->id],
            ['reason' => $validated['staff_response']],
            'Edit request rejected by ' . auth()->user()->name
        );

        // Notify the parishioner
        $certificate = $editRequest->certificate;
        $linkedUser  = \App\Models\User::where('parishioner_id', $certificate->parishioner_id)->first();
        if ($linkedUser) {
            try {
                $linkedUser->notify(new \App\Notifications\ParishionerStatusNotification(
                    'Correction Request Not Approved',
                    'Your correction request for the ' . $certificate->getTypeLabel()
                        . ' was not approved. Reason: ' . $validated['staff_response'],
                    route('parishioner.certificates.index'),
                    'document'
                ));
            } catch (\Exception $e) {
                \Log::warning('Edit request rejection notification failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Correction request rejected. Parishioner has been notified.');
    }
}
