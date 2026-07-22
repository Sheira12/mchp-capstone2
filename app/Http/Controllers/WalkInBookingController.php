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

        return view('walkin.index', compact('services'));
    }

    /**
     * Process the walk-in booking form — no login required.
     * Creates or finds a parishioner record, then creates a booking.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Parishioner info
            'first_name'     => ['required', 'string', 'max:100'],
            'last_name'      => ['required', 'string', 'max:100'],
            'middle_name'    => ['nullable', 'string', 'max:100'],
            'contact_number' => ['required', 'string', 'max:20'],
            'address'        => ['nullable', 'string', 'max:255'],
            'barangay'       => ['nullable', 'string', 'max:100'],
            'email'          => ['nullable', 'email', 'max:255'],
            // Booking info
            'booking_type'   => ['required', 'string'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

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
        ]);

        // Generate QR code
        app(QrCodeService::class)->generateForBooking($booking);

        // Notify admin users (database notification)
        $adminUsers = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();
        foreach ($adminUsers as $admin) {
            $admin->notify(new \App\Notifications\AdminBookingNotification($booking));
        }

        // Log for audit
        \App\Models\AuditLog::record(
            'walkin_booking',
            $booking,
            [],
            $booking->toArray(),
            "Walk-in booking created for {$parishioner->full_name}"
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
            $svg = Storage::disk('public')->get($booking->qrCode->qr_image_path);
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
            $svg = Storage::disk('public')->get($booking->qrCode->qr_image_path);
            if ($svg) {
                $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }

        return view('walkin.print', compact('booking', 'qrBase64'));
    }
}
