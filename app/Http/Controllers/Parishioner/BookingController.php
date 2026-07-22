<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Notifications\BookingStatusNotification;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

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
        $services = Service::where('is_bookable', true)->where('is_active', true)
            ->orderBy('sort_order')->get()->groupBy('category');

        return view('parishioner.bookings.create', compact('services'));
    }

    public function store(Request $request)
    {
        $parishioner = auth()->user()->parishioner;

        if (!$parishioner) {
            return redirect()->route('parishioner.profile')->with('info', 'Please complete your profile first.');
        }

        $validated = $request->validate([
            'booking_type'   => ['required', 'string'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'address'        => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        // Conflict detection
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
        $validated['service_fee']    = $service?->fee ?? 0;
        $validated['parishioner_id'] = $parishioner->id;
        $validated['status']         = 'pending'; // Always start as pending

        $booking = Booking::create($validated);

        // Generate QR code
        app(QrCodeService::class)->generateForBooking($booking);

        // Notify parishioner via email
        $linkedUser = \App\Models\User::where('parishioner_id', $booking->parishioner_id)->first();
        if ($linkedUser) {
            $linkedUser->notify(new BookingStatusNotification($booking, 'created'));
        }

        // Notify ALL admin users (database notification — shows in admin bell)
        $adminUsers = \App\Models\User::role(['super_admin', 'parish_secretary'])->get();
        foreach ($adminUsers as $admin) {
            $admin->notify(new \App\Notifications\AdminBookingNotification($booking));
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
}
