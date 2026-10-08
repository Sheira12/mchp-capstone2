<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRequirement;
use App\Models\Service;
use App\Models\ServiceRequirement;
use Illuminate\Http\Request;

class ServiceRequirementController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    //  REQUIREMENT TEMPLATES (per service)
    // ─────────────────────────────────────────────────────────────────────────

    /** List requirement templates for a service. */
    public function index(Service $service)
    {
        $requirements = $service->allServiceRequirements()->get();
        return view('admin.services.requirements.index', compact('service', 'requirements'));
    }

    /** Show create form. */
    public function create(Service $service)
    {
        return view('admin.services.requirements.create', compact('service'));
    }

    /** Store a new requirement template. */
    public function store(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'type'                => ['required', 'in:file,checkbox,text'],
            'is_required'         => ['boolean'],
            'accepted_file_types' => ['nullable', 'string', 'max:100'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['service_id']  = $service->id;
        $validated['is_required'] = $request->boolean('is_required');
        $validated['is_active']   = true;
        $validated['sort_order']  = $validated['sort_order'] ?? 0;

        ServiceRequirement::create($validated);

        return redirect()->route('admin.services.requirements.index', $service)
            ->with('success', 'Requirement added successfully.');
    }

    /** Show edit form. */
    public function edit(Service $service, ServiceRequirement $requirement)
    {
        return view('admin.services.requirements.edit', compact('service', 'requirement'));
    }

    /** Update requirement template. */
    public function update(Request $request, Service $service, ServiceRequirement $requirement)
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'type'                => ['required', 'in:file,checkbox,text'],
            'is_required'         => ['boolean'],
            'accepted_file_types' => ['nullable', 'string', 'max:100'],
            'sort_order'          => ['nullable', 'integer', 'min:0'],
            'is_active'           => ['boolean'],
        ]);

        $validated['is_required'] = $request->boolean('is_required');
        $validated['is_active']   = $request->boolean('is_active');

        $requirement->update($validated);

        return redirect()->route('admin.services.requirements.index', $service)
            ->with('success', 'Requirement updated.');
    }

    /** Soft-deactivate (not hard-delete, preserve history). */
    public function destroy(Service $service, ServiceRequirement $requirement)
    {
        $requirement->update(['is_active' => false]);

        return redirect()->route('admin.services.requirements.index', $service)
            ->with('success', 'Requirement deactivated.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  BOOKING REQUIREMENTS REVIEW QUEUE
    // ─────────────────────────────────────────────────────────────────────────

    /** Admin review queue — all pending/needs_revision submissions. */
    public function reviewQueue(Request $request)
    {
        $query = BookingRequirement::with([
            'booking.parishioner',
            'requirement.service',
            'reviewedBy',
        ]);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        } else {
            // Default: show pending + needs_revision
            $query->whereIn('status', ['pending', 'needs_revision']);
        }

        if ($service = $request->get('service')) {
            $query->whereHas('requirement.service', fn($q) => $q->where('slug', $service));
        }

        if ($from = $request->get('date_from')) {
            $query->where('submitted_at', '>=', $from);
        }
        if ($to = $request->get('date_to')) {
            $query->where('submitted_at', '<=', $to . ' 23:59:59');
        }

        $submissions = $query->orderByDesc('submitted_at')->paginate(20)->withQueryString();
        $services    = Service::where('is_active', true)->orderBy('name')->get();

        // Count badges
        $pendingCount = BookingRequirement::where('status', 'pending')->count();

        return view('admin.booking-requirements.index', compact('submissions', 'services', 'pendingCount'));
    }

    /** Show a single booking's full requirements. */
    public function showBookingRequirements(Booking $booking)
    {
        $booking->load(['parishioner', 'bookingRequirements.requirement.service', 'bookingRequirements.reviewedBy']);
        $service = Service::where('slug', $booking->booking_type)->first();

        return view('admin.booking-requirements.show', compact('booking', 'service'));
    }

    /** Approve a single requirement item. */
    public function approve(Request $request, BookingRequirement $bookingRequirement)
    {
        $request->validate([
            'admin_remark' => ['nullable', 'string', 'max:500'],
        ]);

        $bookingRequirement->update([
            'status'       => 'approved',
            'admin_remark' => $request->get('admin_remark'),
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
        ]);

        // Check if all required items are now approved → auto-approve the booking requirements
        $booking  = $bookingRequirement->booking;
        $progress = $booking->requirementsProgress();

        if ($progress['total'] > 0 && $progress['approved'] >= $progress['total']) {
            if (!$booking->requirements_approved_at) {
                $booking->update([
                    'requirements_approved_at' => now(),
                    'requirements_approved_by' => auth()->id(),
                ]);

                // Notify parishioner
                $this->notifyParishioner($booking, 'all_approved');
            }
        } else {
            // Notify on individual item approved
            $this->notifyParishioner($booking, 'item_approved', $bookingRequirement);
        }

        // Audit log
        \App\Models\AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'approved',
            'model_type' => BookingRequirement::class,
            'model_id'   => $bookingRequirement->id,
            'new_values' => ['status' => 'approved', 'remark' => $request->get('admin_remark')],
            'ip_address' => $request->ip(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'all_approved' => $booking->fresh()->requirementsApproved()]);
        }

        return back()->with('success', 'Requirement approved.');
    }

    /** Reject / request revision on a single item. */
    public function requestRevision(Request $request, BookingRequirement $bookingRequirement)
    {
        $request->validate([
            'admin_remark' => ['required', 'string', 'max:500'],
        ]);

        $bookingRequirement->update([
            'status'       => 'needs_revision',
            'admin_remark' => $request->get('admin_remark'),
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
        ]);

        // If previously all-approved, reset the gate
        $booking = $bookingRequirement->booking;
        if ($booking->requirements_approved_at) {
            $booking->update([
                'requirements_approved_at' => null,
                'requirements_approved_by' => null,
            ]);
        }

        $this->notifyParishioner($booking, 'needs_revision', $bookingRequirement);

        \App\Models\AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'requested_revision',
            'model_type' => BookingRequirement::class,
            'model_id'   => $bookingRequirement->id,
            'new_values' => ['status' => 'needs_revision', 'remark' => $request->get('admin_remark')],
            'ip_address' => $request->ip(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Revision requested. Parishioner has been notified.');
    }

    /** Manually mark all requirements as approved for a booking. */
    public function approveAll(Request $request, Booking $booking)
    {
        $booking->bookingRequirements()->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $booking->update([
            'requirements_approved_at' => now(),
            'requirements_approved_by' => auth()->id(),
        ]);

        $this->notifyParishioner($booking, 'all_approved');

        return back()->with('success', 'All requirements approved. Parishioner can now complete their booking.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function notifyParishioner(Booking $booking, string $event, ?BookingRequirement $item = null): void
    {
        try {
            $linkedUser = \App\Models\User::where('parishioner_id', $booking->parishioner_id)->first();
            if (!$linkedUser) return;

            $notification = new \App\Notifications\RequirementStatusNotification($booking, $event, $item);
            $linkedUser->notify($notification);
            $notification->sendEmail($linkedUser);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Requirement notification failed: ' . $e->getMessage());
        }
    }
}
