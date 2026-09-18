<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateEditRequest;
use App\Models\CertificateStatusHistory;
use Illuminate\Http\Request;

class CertificateEditRequestController extends Controller
{
    /**
     * Show the edit form for a verified certificate.
     * Parishioners can review and correct sponsor/detail fields.
     */
    public function create(Certificate $certificate)
    {
        $this->authorizeCertificate($certificate);

        if ($certificate->record_verification_status !== 'verified') {
            return back()->with('error', 'You can only submit corrections for verified certificates.');
        }

        // Load existing pending edit request if any
        $pendingRequest = $certificate->editRequests()
            ->where('status', 'pending')
            ->latest()
            ->first();

        $certificate->load('sacramentalRecord');

        return view('parishioner.certificates.edit-request', compact('certificate', 'pendingRequest'));
    }

    /**
     * Submit a correction request.
     * Does NOT touch sacramental_records — saves to certificate_edit_requests.
     */
    public function store(Request $request, Certificate $certificate)
    {
        $this->authorizeCertificate($certificate);

        if ($certificate->record_verification_status !== 'verified') {
            return back()->with('error', 'You can only submit corrections for verified certificates.');
        }

        // Block if there's already a pending request for this certificate
        $hasPending = $certificate->editRequests()->where('status', 'pending')->exists();
        if ($hasPending) {
            return back()->with('error', 'You already have a correction request awaiting staff review for this certificate.');
        }

        $validated = $request->validate([
            'celebrant'         => ['nullable', 'string', 'max:255'],
            'venue'             => ['nullable', 'string', 'max:255'],
            'date_administered' => ['nullable', 'date'],
            'parents_names'     => ['nullable', 'string', 'max:500'],
            'spouse_name'       => ['nullable', 'string', 'max:255'],
            'ninong'            => ['nullable', 'array'],
            'ninong.*'          => ['nullable', 'string', 'max:255'],
            'ninang'            => ['nullable', 'array'],
            'ninang.*'          => ['nullable', 'string', 'max:255'],
            'sponsors'          => ['nullable', 'array'],
            'sponsors.*'        => ['nullable', 'string', 'max:255'],
            'witnesses'         => ['nullable', 'array'],
            'witnesses.*'       => ['nullable', 'string', 'max:255'],
            'register_number'   => ['nullable', 'string', 'max:100'],
            'page_number'       => ['nullable', 'string', 'max:20'],
            'line_number'       => ['nullable', 'string', 'max:20'],
            'parishioner_note'  => ['nullable', 'string', 'max:1000'],
        ]);

        // Build the changes diff — only include non-empty fields
        $changes = [];

        $simpleFields = ['celebrant', 'venue', 'date_administered', 'parents_names',
                         'spouse_name', 'register_number', 'page_number', 'line_number'];
        foreach ($simpleFields as $field) {
            if (!empty($validated[$field])) {
                $changes[$field] = $validated[$field];
            }
        }

        // Merge ninong/ninang into sponsors array for marriage; keep separate for baptism
        $ninong   = array_values(array_filter($validated['ninong']   ?? [], fn($v) => trim($v ?? '') !== ''));
        $ninang   = array_values(array_filter($validated['ninang']   ?? [], fn($v) => trim($v ?? '') !== ''));
        $sponsors = array_values(array_filter($validated['sponsors'] ?? [], fn($v) => trim($v ?? '') !== ''));
        $witnesses = array_values(array_filter($validated['witnesses'] ?? [], fn($v) => trim($v ?? '') !== ''));

        if (!empty($ninong))   $changes['ninong']    = $ninong;
        if (!empty($ninang))   $changes['ninang']    = $ninang;
        if (!empty($sponsors)) $changes['sponsors']  = $sponsors;
        if (!empty($witnesses)) $changes['witnesses'] = $witnesses;

        if (empty($changes)) {
            return back()->with('error', 'No changes were submitted. Please fill in at least one field to correct.');
        }

        CertificateEditRequest::create([
            'certificate_id'    => $certificate->id,
            'submitted_by'      => auth()->id(),
            'requested_changes' => $changes,
            'parishioner_note'  => $validated['parishioner_note'] ?? null,
            'status'            => 'pending',
        ]);

        // Notify admins
        $admins = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();
        foreach ($admins as $admin) {
            try {
                $admin->notify(new \App\Notifications\AdminCertificateNotification($certificate));
            } catch (\Exception $e) {
                \Log::warning('Edit request admin notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('parishioner.certificates.index')
            ->with('success', 'Your correction request has been submitted. The parish office will review it within 1–3 working days.');
    }

    /**
     * Cancel a pending request (only while still pending).
     */
    public function cancel(CertificateEditRequest $editRequest)
    {
        if ($editRequest->submittedBy->id !== auth()->id()) {
            abort(403);
        }
        if ($editRequest->status !== 'pending') {
            return back()->with('error', 'This request has already been reviewed and cannot be cancelled.');
        }
        $editRequest->delete();
        return back()->with('success', 'Correction request cancelled.');
    }

    // ── Private ────────────────────────────────────────────────────────────

    private function authorizeCertificate(Certificate $certificate): void
    {
        if ($certificate->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403, 'This certificate does not belong to your account.');
        }
    }
}
