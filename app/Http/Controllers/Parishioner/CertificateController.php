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
        $sacramentalRecords = $parishioner->sacramentalRecords()->orderBy('date_administered')->get();

        $certificateTypes = Certificate::TYPES;

        return view('parishioner.certificates.create', compact('parishioner', 'sacramentalRecords', 'certificateTypes'));
    }

    /**
     * Submit a certificate request (creates a draft certificate for admin review).
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

        // Verify the sacramental record belongs to this parishioner
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

        $certificate = \DB::transaction(function () use ($validated, $parishioner) {
            return Certificate::create([
                'parishioner_id'        => $parishioner->id,
                'sacramental_record_id' => $validated['sacramental_record_id'] ?? null,
                'type'                  => $validated['type'],
                'issued_date'           => now()->toDateString(),
                'purpose'               => $validated['purpose'],
                'notes'                 => $validated['notes'] ?? null,
                'status'                => 'draft',
            ]);
        });

        // Notify ALL admin users (shows in admin notification bell)
        $adminUsers = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();
        foreach ($adminUsers as $admin) {
            $admin->notify(new \App\Notifications\AdminCertificateNotification($certificate));
        }

        // Notify the parishioner (shows in portal notification bell)
        auth()->user()->notify(new \App\Notifications\ParishionerStatusNotification(
            'Certificate Request Received',
            'Your request for a ' . $certificate->getTypeLabel() . ' has been submitted. We will process it within 1–3 working days.',
            route('parishioner.certificates.index'),
            'document'
        ));

        return redirect()->route('parishioner.certificates.index')
            ->with('success', 'Certificate request submitted successfully. The parish office will process it within 1–3 working days.');
    }

    public function download(Certificate $certificate)
    {
        // Ensure the certificate belongs to the authenticated parishioner
        if ($certificate->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403, 'This certificate does not belong to your account.');
        }

        // Allow download for both issued and released status
        if (!in_array($certificate->status, ['issued', 'released'])) {
            return back()->withErrors(['error' => 'This certificate is not yet ready for download.']);
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
