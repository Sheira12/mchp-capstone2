<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Parishioner;
use App\Models\Service;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WalkInBookingController extends Controller
{
    /**
     * Show the public walk-in booking kiosk form.
     */
    public function index()
    {
        $services = Service::where('is_bookable', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        // Pre-load requirements per service slug for the JS checklist
        // Only gate-type requirements (seminar/document/sacrament_record)
        $serviceRequirements = [];
        try {
            foreach ($services->flatten() as $svc) {
                $reqs = \App\Models\ServiceRequirement::where(function ($q) use ($svc) {
                        $q->where('service_slug', $svc->slug)
                          ->orWhere('service_id', $svc->id);
                    })
                    ->where('is_active', true)
                    ->whereIn('eligibility_type', ['seminar', 'document', 'sacrament_record'])
                    ->orderBy('sort_order')
                    ->get(['id', 'name', 'description', 'eligibility_type', 'is_required', 'requires_proof']);

                if ($reqs->isNotEmpty()) {
                    $serviceRequirements[$svc->slug] = $reqs->toArray();
                }
            }
        } catch (\Exception $e) {
            // If service_requirements table doesn't exist yet, fail silently
            $serviceRequirements = [];
        }

        return view('walkin.index', compact('services', 'serviceRequirements'));
    }

    /**
     * Process the walk-in booking form — no login required.
     * Creates or finds a parishioner record, then creates a booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Parishioner info
            'first_name'            => ['required', 'string', 'max:100'],
            'last_name'             => ['required', 'string', 'max:100'],
            'middle_name'           => ['nullable', 'string', 'max:100'],
            'contact_number'        => ['required', 'string', 'max:20'],
            'address'               => ['nullable', 'string', 'max:255'],
            'barangay'              => ['nullable', 'string', 'max:100'],
            'email'                 => ['nullable', 'email', 'max:255'],
            // Booking info
            'booking_type'          => ['required', 'string'],
            'scheduled_date'        => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time'        => ['nullable', 'date_format:H:i'],
            'notes'                 => ['nullable', 'string', 'max:500'],
            // Requirements checklist ticks
            'walkin_requirements'   => ['nullable', 'array'],
            'walkin_requirements.*' => ['integer', 'exists:service_requirements,id'],
            // Staff override
            'staff_override_reason' => ['nullable', 'string', 'max:500'],
        ]);

        // ── Server-side requirements gate for walk-in ────────────────────────
        // Check if all REQUIRED gate requirements are either ticked OR
        // a staff override reason is provided.
        $overrideReason = trim($request->input('staff_override_reason', ''));
        $tickedIds      = $request->input('walkin_requirements', []);

        if (empty($overrideReason)) {
            try {
                $requiredReqs = \App\Models\ServiceRequirement::where(function ($q) use ($validated) {
                        $q->where('service_slug', $validated['booking_type'])
                          ->orWhereHas('service', fn($sq) => $sq->where('slug', $validated['booking_type']));
                    })
                    ->where('is_active', true)
                    ->whereIn('eligibility_type', ['seminar', 'document', 'sacrament_record'])
                    ->where('is_required', true)
                    ->pluck('id')
                    ->toArray();

                $missingTicks = array_diff($requiredReqs, array_map('intval', $tickedIds));

                if (!empty($missingTicks)) {
                    $missingNames = \App\Models\ServiceRequirement::whereIn('id', $missingTicks)
                        ->pluck('name')
                        ->implode(', ');
                    return back()->withInput()->withErrors([
                        'walkin_requirements' => "Please tick all required items as presented in person, or provide a staff override reason. Missing: {$missingNames}",
                    ]);
                }
            } catch (\Exception $e) {
                // If table doesn't exist yet, skip gate check
            }
        }

        // Find existing parishioner or create new one
        $parishioner = Parishioner::where('first_name', $validated['first_name'])
            ->where('last_name', $validated['last_name'])
            ->where('contact_number', $validated['contact_number'])
            ->first();

        if (!$parishioner) {
            $parishioner = Parishioner::create([
                'first_name'     => $validated['first_name'],
                'last_name'      => $validated['last_name'],
                'middle_name'    => $validated['middle_name'] ?? null,
                'contact_number' => $validated['contact_number'],
                'address'        => $validated['address'] ?? null,
                'barangay'       => $validated['barangay'] ?? null,
                'city'           => 'Cabuyao',
                'province'       => 'Laguna',
                'email'          => $validated['email'] ?? null,
                'is_active'      => true,
            ]);
        }

        // Check for scheduling conflict
        $conflict = Booking::where('scheduled_date', $validated['scheduled_date'])
            ->where('scheduled_time', $validated['scheduled_time'])
            ->where('booking_type', $validated['booking_type'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors([
                'scheduled_time' => 'This time slot is already taken. Please choose another time.',
            ]);
        }

        // Get service fee
        $service = Service::where('slug', $validated['booking_type'])->first();

        // Create the booking
        $booking = Booking::create([
            'parishioner_id' => $parishioner->id,
            'booking_type'   => $validated['booking_type'],
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'] ?? null,
            'service_fee'    => $service?->fee ?? 0,
            'address'        => $validated['address'] ?? null,
            'notes'          => ($validated['notes'] ?? null)
                ? 'Walk-in booking. ' . $validated['notes']
                : 'Walk-in booking submitted at parish office.',
            'status'         => 'pending',
            'admin_notes'    => $overrideReason
                ? "[STAFF OVERRIDE] {$overrideReason}"
                : (empty($tickedIds) ? null : '[REQUIREMENTS VERIFIED IN PERSON: ' . implode(', ', \App\Models\ServiceRequirement::whereIn('id', $tickedIds)->pluck('name')->toArray()) . ']'),
        ]);

        // Generate QR code
        app(QrCodeService::class)->generateForBooking($booking);

        // Notify admin users (database notification)
        $adminUsers = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();
        foreach ($adminUsers as $admin) {
            try {
                $admin->notify(new \App\Notifications\AdminBookingNotification($booking));
            } catch (\Exception $e) {
                \Log::warning('WalkIn admin notification failed: ' . $e->getMessage());
            }
        }

        // Log for audit
        \App\Models\AuditLog::record(
            'walkin_booking',
            $booking,
            [],
            array_merge($booking->toArray(), [
                'requirements_ticked' => $tickedIds,
                'staff_override'      => $overrideReason ?: null,
            ]),
            "Walk-in booking created for {$parishioner->full_name}"
            . ($overrideReason ? " [OVERRIDE: {$overrideReason}]" : '')
        );

        return redirect()->route('walkin.confirmation', $booking)
            ->with('success', 'Booking submitted successfully!');
    }

    /**
     * Show booking confirmation with QR stub.
     */
    public function confirmation(Booking $booking)
    {
        $booking->load(['parishioner', 'qrCode']);

        // Generate QR base64
        $qrBase64 = null;
        if ($booking->qrCode?->qr_image_path) {
            $svg = Storage::disk('supabase')->get($booking->qrCode->qr_image_path);
            if ($svg) {
                $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }

        return view('walkin.confirmation', compact('booking', 'qrBase64'));
    }

    /**
     * Print the walk-in stub (print-friendly version).
     */
    public function printStub(Booking $booking)
    {
        $booking->load(['parishioner', 'qrCode']);

        $qrBase64 = null;
        if ($booking->qrCode?->qr_image_path) {
            $svg = Storage::disk('supabase')->get($booking->qrCode->qr_image_path);
            if ($svg) {
                $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }

        return view('walkin.print', compact('booking', 'qrBase64'));
    }
}
