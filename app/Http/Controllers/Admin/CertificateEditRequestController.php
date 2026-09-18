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
     * Approve a correction request — applies changes to sacramental_record, regenerates PDF.
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

            // Apply changes to sacramental record if one is linked
            if ($record) {
                $update = [];

                if (isset($changes['date_administered'])) $update['date_administered'] = $changes['date_administered'];
                if (isset($changes['celebrant']))         $update['celebrant']         = $changes['celebrant'];
                if (isset($changes['venue']))             $update['venue']             = $changes['venue'];
                if (isset($changes['register_number']))   $update['register_number']   = $changes['register_number'];
                if (isset($changes['page_number']))       $update['page_number']       = $changes['page_number'];
                if (isset($changes['line_number']))       $update['line_number']        = $changes['line_number'];
                if (isset($changes['notes']))             $update['notes']             = $changes['notes'];

                // Handle sponsor/godparent arrays
                if (isset($changes['ninong']) || isset($changes['ninang'])) {
                    $ninong = $changes['ninong'] ?? [];
                    $ninang = $changes['ninang'] ?? [];
                    // Store as a flat array: first come ninong, then ninang
                    // Tag each so the template can split them properly
                    $godparents = array_merge(
                        array_map(fn($n) => 'NINONG:' . $n, $ninong),
                        array_map(fn($n) => 'NINANG:' . $n, $ninang)
                    );
                    if (!empty($godparents)) $update['godparents'] = $godparents;
                }

                if (isset($changes['sponsors'])) {
                    $sponsors = array_values(array_filter($changes['sponsors']));
                    if (!empty($sponsors)) $update['sponsors'] = $sponsors;
                }

                if (isset($changes['witnesses'])) {
                    $witnesses = array_values(array_filter($changes['witnesses']));
                    if (!empty($witnesses)) $update['witnesses'] = $witnesses;
                }

                if (!empty($update)) {
                    $record->update($update);
                }
            }

            // Mark the edit request as approved
            $editRequest->update([
                'status'          => 'approved',
                'reviewed_by'     => auth()->id(),
                'reviewed_at'     => now(),
                'staff_response'  => $validated['staff_response'] ?? 'Approved by ' . auth()->user()->name,
            ]);

            AuditLog::record('approve_edit_request', $certificate,
                ['edit_request_id' => $editRequest->id],
                $changes,
                'Edit request approved by ' . auth()->user()->name
            );
        });

        // Re-generate PDF with updated data
        set_time_limit(120);
        $certificate = $editRequest->certificate->fresh(['parishioner', 'sacramentalRecord', 'issuedBy', 'qrCode']);
        try {
            $this->certificateService->generate($certificate);
        } catch (\Exception $e) {
            \Log::error('PDF re-generation after edit approval failed: ' . $e->getMessage());
        }

        // Notify the parishioner
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
