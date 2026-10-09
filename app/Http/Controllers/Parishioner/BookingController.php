<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRequirement;
use App\Models\Service;
use App\Models\ServiceRequirement;
use App\Notifications\BookingStatusNotification;
use App\Services\EligibilityService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    public function index()
    {
        $parishioner = auth()->user()->parishioner;

        if (!$parishioner) {
            return redirect()->route('parishioner.profile')->with('info', 'Please complete your profile first.');
        }

        $bookings = $parishioner->bookings()->orderByDesc('scheduled_date')->paginate(10);

        return view('parishioner.bookings.index', compact('bookings'));
    }

    public function create()
    {
        $parishioner = auth()->user()->parishioner;
        if (!$parishioner) {
            return redirect()->route('parishioner.profile')->with('info', 'Please complete your profile first.');
        }

        $services = Service::where('is_bookable', true)->where('is_active', true)
            ->orderBy('sort_order')->get()->groupBy('category');

        // Pre-select service if coming from eligibility page
        $preselect  = request('preselect');
        $preService = $preselect ? Service::where('slug', $preselect)->first() : null;

        // Pre-compute eligibility per service so the view can show badges and lock ineligible ones
        $eligibilityResults = [];
        try {
            $eligSvc = app(EligibilityService::class);
            foreach ($services->flatten() as $svc) {
                if ($svc->hasEligibilityRules()) {
                    $eligibilityResults[$svc->slug] = $eligSvc->check($parishioner, $svc->slug)->toArray();
                }
            }
        } catch (\Exception $e) {
            // fail-open — eligibility info is decorative here; gate is enforced on store()
        }

        // If resuming from requirements page
        $preBooking = request('booking_id') ? Booking::find(request('booking_id')) : null;
        if ($preBooking && ($preBooking->parishioner_id !== $parishioner->id || !$preBooking->requirementsApproved())) {
            $preBooking = null;
        }

        return view('parishioner.bookings.create', compact(
            'services', 'preService', 'preBooking', 'eligibilityResults'
        ));
    }

    public function store(Request $request)
    {
        $parishioner = auth()->user()->parishioner;

        if (!$parishioner) {
            return redirect()->route('parishioner.profile')->with('info', 'Please complete your profile first.');
        }

        $validated = $request->validate([
            'booking_type'   => ['required', 'string', 'in:' . implode(',', array_keys(\App\Models\Booking::TYPES))],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'location_type'  => ['nullable', 'in:in_church,off_site'],
            'address'        => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'contact_phone'  => ['nullable', 'string', 'max:20'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'booking_id'     => ['nullable', 'exists:bookings,id'],  // resume from requirements step
        ]);

        // ── SERVER-SIDE REQUIREMENTS GATE ────────────────────────────────────
        // If the service has required requirements, check they are all approved.
        $service = Service::where('slug', $validated['booking_type'])->first();

        // ── SERVER-SIDE ELIGIBILITY GATE (runs BEFORE requirements gate) ─────
        // Check that the parishioner satisfies all eligibility rules for this service.
        if ($service && $service->hasEligibilityRules()) {
            try {
                $eligResult = app(EligibilityService::class)->check($parishioner, $validated['booking_type']);
                if (!$eligResult->isEligible()) {
                    $blocking = $eligResult->blocking()->map(fn($r) => $r->ruleName)->implode(', ');
                    return redirect()
                        ->route('parishioner.eligibility.index')
                        ->with('error', "You are not yet eligible to book {$service->name}. Please complete the required prerequisites first. Missing: {$blocking}.");
                }
            } catch (\Exception $e) {
                \Log::warning('Eligibility gate check failed (fail-open): ' . $e->getMessage());
                // fail-open — don't block on unexpected error
            }
        }

        if ($service && $service->requiresPreApproval()) {
            // Check if parishioner has a pre-approved booking stub for this service
            $approvedBooking = null;
            if (!empty($validated['booking_id'])) {
                $approvedBooking = Booking::where('id', $validated['booking_id'])
                    ->where('parishioner_id', $parishioner->id)
                    ->whereNotNull('requirements_approved_at')
                    ->first();
            }

            // Also check for any approved booking stub they may already have
            if (!$approvedBooking) {
                $approvedBooking = Booking::where('parishioner_id', $parishioner->id)
                    ->where('booking_type', $validated['booking_type'])
                    ->whereNotNull('requirements_approved_at')
                    ->whereNull('scheduled_date')  // stub — no date yet
                    ->latest()
                    ->first();
            }

            if (!$approvedBooking) {
                return redirect()
                    ->route('parishioner.bookings.requirements', $service)
                    ->with('error', 'You must submit and have all required documents approved before booking this service.');
            }

            // Use the approved stub's ID so requirements stay linked
            $stubId = $approvedBooking->id;
        }

        // Conflict detection — checks overlapping time slots including service duration + 30-min buffer
        $conflict = \App\Models\Booking::where('scheduled_date', $validated['scheduled_date'])
            ->where('booking_type', $validated['booking_type'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($validated['scheduled_time'], function ($q) use ($validated) {
                // Get service duration; default 60 min + 30 min buffer
                $service      = \App\Models\Service::where('slug', $validated['booking_type'])->first();
                $durationMins = ($service?->duration_minutes ?? 60) + 30;

                $startNew = \Carbon\Carbon::createFromFormat('H:i', $validated['scheduled_time']);
                $endNew   = $startNew->copy()->addMinutes($durationMins);

                // A conflict exists if the existing booking's time-window overlaps the new one.
                // Overlap: existing_start < new_end AND existing_end > new_start
                $q->whereNotNull('scheduled_time')
                  ->whereRaw('scheduled_time::time < ?', [$endNew->format('H:i:s')])
                  ->whereRaw(
                      "scheduled_time::time + (COALESCE((SELECT duration_minutes FROM services WHERE slug = bookings.booking_type LIMIT 1), 60) + 30) * interval '1 minute' > ?",
                      [$startNew->format('H:i:s')]
                  );
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors([
                'scheduled_time' => 'This time slot is already taken. Please choose another time or date.',
            ]);
        }

        // Get service fee
        $service = Service::where('slug', $validated['booking_type'])->first();
        $validated['service_fee']    = $service?->fee ?? 0;
        $validated['parishioner_id'] = $parishioner->id;
        $validated['status']         = 'pending'; // Always start as pending

        unset($validated['booking_id']); // don't pass internal field to create()

        // If we have an approved stub, update it with the date instead of creating a new booking
        if (!empty($stubId)) {
            $booking = Booking::find($stubId);
            $booking->update([
                'scheduled_date' => $validated['scheduled_date'],
                'scheduled_time' => $validated['scheduled_time'] ?? null,
                'location_type'  => $validated['location_type'] ?? 'in_church',
                'address'        => $validated['address'] ?? null,
                'contact_person' => $validated['contact_person'] ?? null,
                'contact_phone'  => $validated['contact_phone'] ?? null,
                'notes'          => $validated['notes'] ?? null,
                'service_fee'    => $service?->fee ?? 0,
            ]);
        } else {
            $booking = Booking::create($validated);
        }

        // Generate QR code
        app(QrCodeService::class)->generateForBooking($booking);

        // Notify parishioner via email
        $linkedUser = \App\Models\User::where('parishioner_id', $booking->parishioner_id)->first();
        if ($linkedUser) {
            try {
                $notification = new BookingStatusNotification($booking, 'created');
                $linkedUser->notify($notification);
                // Send email via HTTP API (non-fatal — booking is already saved)
                $notification->sendEmail($linkedUser);
            } catch (\Exception $e) {
                \Log::warning('Booking created notification failed: ' . $e->getMessage());
            }
        }

        // Notify ALL admin users (database notification — shows in admin bell)
        $adminUsers = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();
        foreach ($adminUsers as $admin) {
            try {
                $admin->notify(new \App\Notifications\AdminBookingNotification($booking));
            } catch (\Exception $e) {
                \Log::warning('Admin booking notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('parishioner.bookings.show', $booking)
            ->with('success', 'Booking submitted! We will confirm it shortly.');
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['payment', 'qrCode']);
        return view('parishioner.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        $request->validate(['cancellation_reason' => ['required', 'string']]);

        if (!in_array($booking->status, ['pending'])) {
            return back()->withErrors(['error' => 'Only pending bookings can be cancelled by parishioners.']);
        }

        $booking->update([
            'status'              => 'cancelled',
            'cancelled_by'        => auth()->id(),
            'cancelled_at'        => now(),
            'cancellation_reason' => $request->get('cancellation_reason'),
        ]);

        return redirect()->route('parishioner.bookings.index')->with('success', 'Booking cancelled.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  REQUIREMENTS FLOW
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Show the requirements checklist for a given service.
     * Creates a "stub" booking (no date) if one doesn't already exist for this parishioner+service.
     */
    public function requirementsForm(Service $service)
    {
        $parishioner = auth()->user()->parishioner;
        if (!$parishioner) {
            return redirect()->route('parishioner.profile')->with('info', 'Please complete your profile first.');
        }

        $requirements = $service->serviceRequirements()->get();

        // Find or create a stub booking (status=pending, no scheduled_date)
        $booking = Booking::where('parishioner_id', $parishioner->id)
            ->where('booking_type', $service->slug)
            ->whereNull('scheduled_date')
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->first();

        if (!$booking) {
            $booking = Booking::create([
                'parishioner_id' => $parishioner->id,
                'booking_type'   => $service->slug,
                'status'         => 'pending',
                'service_fee'    => $service->fee ?? 0,
                'scheduled_date' => null,
            ]);

            // Create placeholder BookingRequirement rows for each active requirement
            foreach ($requirements as $req) {
                BookingRequirement::firstOrCreate([
                    'booking_id'             => $booking->id,
                    'service_requirement_id' => $req->id,
                ]);
            }
        } else {
            // Ensure rows exist for any newly-added requirements
            foreach ($requirements as $req) {
                BookingRequirement::firstOrCreate([
                    'booking_id'             => $booking->id,
                    'service_requirement_id' => $req->id,
                ]);
            }
        }

        $bookingRequirements = $booking->bookingRequirements()->with('requirement')->get()
            ->keyBy('service_requirement_id');

        return view('parishioner.bookings.requirements', compact(
            'service', 'requirements', 'booking', 'bookingRequirements'
        ));
    }

    /**
     * Handle file/text/checkbox submission for a single requirement item.
     */
    public function uploadRequirement(Request $request, Booking $booking, ServiceRequirement $req)
    {
        $this->authorize('view', $booking);

        // Validate based on type
        $rules = ['parishioner_note' => ['nullable', 'string', 'max:500']];

        if ($req->type === 'file') {
            $rules['file'] = ['required', 'file', 'max:10240'];  // 10MB max
        } elseif ($req->type === 'checkbox') {
            $rules['text_value'] = ['required', 'string'];
        } elseif ($req->type === 'text') {
            $rules['text_value'] = ['required', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules);

        $item = BookingRequirement::firstOrCreate([
            'booking_id'             => $booking->id,
            'service_requirement_id' => $req->id,
        ]);

        $update = [
            'status'           => 'pending',
            'parishioner_note' => $validated['parishioner_note'] ?? null,
            'submitted_at'     => now(),
            'admin_remark'     => null,   // reset previous remark on resubmit
        ];

        if ($req->type === 'file' && $request->hasFile('file')) {
            // Delete old file if exists
            if ($item->file_path) {
                try { Storage::disk('supabase')->delete($item->file_path); } catch (\Exception $e) {}
            }
            $path = $request->file('file')->store(
                'booking-requirements/' . $booking->id,
                'supabase'
            );
            $update['file_path'] = $path;
        } elseif (in_array($req->type, ['checkbox', 'text'])) {
            $update['text_value'] = $validated['text_value'];
        }

        $item->update($update);

        // Notify admins of new submission
        try {
            $admins = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AdminBookingRequirementNotification($booking, $item));
            }
        } catch (\Exception $e) {
            \Log::warning('Admin requirement notification failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('parishioner.bookings.requirements', $booking->service ?? $req->service)
            ->with('success', 'Requirement submitted! The parish office will review it shortly.');
    }

    /**
     * Show full requirement status for an existing booking.
     */
    public function bookingRequirements(Booking $booking)
    {
        $this->authorize('view', $booking);
        $booking->load(['bookingRequirements.requirement.service']);

        $service      = Service::where('slug', $booking->booking_type)->first();
        $requirements = $service?->serviceRequirements()->get() ?? collect();
        $bookingRequirements = $booking->bookingRequirements->keyBy('service_requirement_id');

        return view('parishioner.bookings.requirements', compact(
            'service', 'requirements', 'booking', 'bookingRequirements'
        ));
    }
}
