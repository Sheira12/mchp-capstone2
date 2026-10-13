<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Parishioner;
use App\Models\ParishionerRequirement;
use App\Models\ServiceRequirement;
use App\Models\Service;
use Illuminate\Http\Request;

class ParishionerRequirementController extends Controller
{
    /**
     * Verification queue — pending self-declared requirements.
     */
    public function index(Request $request)
    {
        $query = ParishionerRequirement::with([
            'parishioner',
            'requirement.service',
            'verifiedBy',
        ]);

        // Default: show pending only
        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by service
        if ($serviceSlug = $request->get('service')) {
            $query->whereHas('requirement', fn($q) => $q->where('service_slug', $serviceSlug));
        }

        // Filter by source
        if ($source = $request->get('source')) {
            $query->where('source', $source);
        }

        // Exclude system_matched rows from the queue (those are auto-verified)
        if ($status === 'pending') {
            $query->whereIn('source', ['self_declared', 'staff_recorded']);
        }

        $submissions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $services     = Service::where('is_active', true)->orderBy('name')->get();
        $pendingCount = ParishionerRequirement::where('status', 'pending')
            ->whereIn('source', ['self_declared', 'staff_recorded'])
            ->count();

        return view('admin.parishioner-requirements.index', compact(
            'submissions', 'services', 'pendingCount', 'status'
        ));
    }

    /**
     * Show a single submission with proof preview.
     */
    public function show(ParishionerRequirement $parishionerRequirement)
    {
        $parishionerRequirement->load(['parishioner', 'requirement.service', 'verifiedBy']);
        return view('admin.parishioner-requirements.show', compact('parishionerRequirement'));
    }

    /**
     * Approve a declaration.
     */
    public function approve(Request $request, ParishionerRequirement $parishionerRequirement)
    {
        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $parishionerRequirement->update([
            'status'           => 'verified',
            'verified_by'      => auth()->id(),
            'verified_at'      => now(),
            'rejection_reason' => null,
        ]);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'parishioner_requirement_approved',
            'model_type' => ParishionerRequirement::class,
            'model_id'   => $parishionerRequirement->id,
            'new_values' => ['status' => 'verified', 'note' => $request->admin_note],
            'ip_address' => $request->ip(),
        ]);

        $this->notifyParishioner($parishionerRequirement, 'verified', $request->admin_note);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Requirement verified. Parishioner has been notified.');
    }

    /**
     * Reject a declaration with a required reason.
     */
    public function reject(Request $request, ParishionerRequirement $parishionerRequirement)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $parishionerRequirement->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'verified_by'      => auth()->id(),
            'verified_at'      => now(),
        ]);

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'parishioner_requirement_rejected',
            'model_type' => ParishionerRequirement::class,
            'model_id'   => $parishionerRequirement->id,
            'new_values' => ['status' => 'rejected', 'reason' => $request->rejection_reason],
            'ip_address' => $request->ip(),
        ]);

        $this->notifyParishioner($parishionerRequirement, 'rejected', $request->rejection_reason);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Declaration rejected. Parishioner has been notified.');
    }

    /**
     * Staff records a requirement directly for a parishioner (source=staff_recorded, auto-verified).
     * Used when the parish itself ran a seminar or has the document on file.
     */
    public function staffRecord(Request $request)
    {
        $request->validate([
            'parishioner_id'         => ['required', 'exists:parishioners,id'],
            'service_requirement_id' => ['required', 'exists:service_requirements,id'],
            'date_completed'         => ['required', 'date', 'before_or_equal:today'],
            'venue_or_organizer'     => ['required', 'string', 'max:255'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
        ]);

        $existing = ParishionerRequirement::where('parishioner_id', $request->parishioner_id)
            ->where('service_requirement_id', $request->service_requirement_id)
            ->where('status', 'verified')
            ->first();

        if ($existing) {
            return back()->withErrors(['service_requirement_id' => 'A verified record already exists for this parishioner and requirement.']);
        }

        $pr = ParishionerRequirement::updateOrCreate(
            [
                'parishioner_id'         => $request->parishioner_id,
                'service_requirement_id' => $request->service_requirement_id,
            ],
            [
                'status'             => 'verified',
                'date_completed'     => $request->date_completed,
                'venue_or_organizer' => $request->venue_or_organizer,
                'notes'              => $request->notes,
                'source'             => 'staff_recorded',
                'verified_by'        => auth()->id(),
                'verified_at'        => now(),
                'rejection_reason'   => null,
            ]
        );

        AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'parishioner_requirement_staff_recorded',
            'model_type' => ParishionerRequirement::class,
            'model_id'   => $pr->id,
            'new_values' => $pr->toArray(),
            'ip_address' => $request->ip(),
        ]);

        $this->notifyParishioner($pr, 'staff_recorded', null);

        return back()->with('success', 'Requirement recorded and verified for the parishioner.');
    }

    /**
     * Show the staff-record form for a specific parishioner.
     */
    public function createStaffRecord(Parishioner $parishioner)
    {
        $services = Service::where('is_bookable', true)->where('is_active', true)
            ->with(['serviceRequirements' => fn($q) =>
                $q->where('is_active', true)
                  ->whereIn('eligibility_type', ['seminar', 'document', 'sacrament_record'])
                  ->orderBy('sort_order')
            ])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn($s) => $s->serviceRequirements->isNotEmpty());

        $existing = ParishionerRequirement::where('parishioner_id', $parishioner->id)
            ->where('status', 'verified')
            ->pluck('service_requirement_id')
            ->toArray();

        return view('admin.parishioner-requirements.staff-record', compact('parishioner', 'services', 'existing'));
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function notifyParishioner(ParishionerRequirement $pr, string $event, ?string $detail): void
    {
        try {
            $user = \App\Models\User::where('parishioner_id', $pr->parishioner_id)->first();
            if (!$user) return;

            $reqName = $pr->requirement?->name ?? 'requirement';
            $svcName = $pr->requirement?->service?->name ?? 'service';

            $msg = match ($event) {
                'verified'       => "Your declaration for \"{$reqName}\" ({$svcName}) has been verified. You may now be eligible to book.",
                'rejected'       => "Your declaration for \"{$reqName}\" ({$svcName}) was rejected. Reason: {$detail}. Please resubmit.",
                'staff_recorded' => "Staff has recorded \"{$reqName}\" ({$svcName}) as verified on your behalf.",
                default          => "Your requirement status has been updated.",
            };

            $user->notify(new \App\Notifications\EligibilityNotification(
                $pr->parishioner,
                $event === 'rejected' ? 'document_revision' : 'document_approved',
                ['service' => $svcName, 'requirement' => $reqName, 'message' => $msg]
            ));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('ParishionerRequirement notify failed: ' . $e->getMessage());
        }
    }
}
