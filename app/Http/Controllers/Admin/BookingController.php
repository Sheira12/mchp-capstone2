<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Parishioner;
use App\Notifications\BookingStatusNotification;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with('parishioner');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('booking_type', $type);
        }

        if ($from = $request->get('date_from')) {
            $query->where('scheduled_date', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->where('scheduled_date', '<=', $to);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('parishioner', fn($q) => $q->search($search))
                  ->orWhere('reference_number', 'like', "%{$search}%");
        }

        $bookings = $query->orderBy('scheduled_date')->orderBy('scheduled_time')
            ->paginate(20)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['parishioner', 'confirmedBy', 'cancelledBy', 'payment']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function create()
    {
        return view('admin.bookings.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateBooking($request);

        // Conflict detection
        $conflict = Booking::where('scheduled_date', $validated['scheduled_date'])
            ->where('scheduled_time', $validated['scheduled_time'])
            ->where('booking_type', $validated['booking_type'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($conflict) {
            return back()->withInput()->withErrors([
                'scheduled_time' => 'This time slot is already booked. Please choose another time.',
            ]);
        }

        $booking = Booking::create($validated);

        // Generate QR code
        app(\App\Services\QrCodeService::class)->generateForBooking($booking);

        // Send confirmation email via linked user
        $linkedUser = \App\Models\User::where('parishioner_id', $booking->parishioner_id)->first();
        if ($linkedUser) {
            $linkedUser->notify(new BookingStatusNotification($booking, 'created'));
        }

        AuditLog::record('create', $booking, [], $booking->toArray(), 'Booking created');

        return redirect()->route('admin.bookings.show', $booking)
            ->with('success', 'Booking created successfully.');
    }

    public function confirm(Request $request, Booking $booking)
    {
        $request->validate(['admin_notes' => ['nullable', 'string']]);

        $booking->update([
            'status'       => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
            'admin_notes'  => $request->get('admin_notes'),
        ]);

        $linkedUser = \App\Models\User::where('parishioner_id', $booking->parishioner_id)->first();
        if ($linkedUser) {
            $linkedUser->notify(new BookingStatusNotification($booking, 'confirmed'));
        }

        AuditLog::record('confirm', $booking, ['status' => 'pending'], ['status' => 'confirmed'], 'Booking confirmed');

        return back()->with('success', 'Booking confirmed.');
    }

    public function complete(Booking $booking)
    {
        $booking->update(['status' => 'completed']);

        AuditLog::record('complete', $booking, ['status' => 'confirmed'], ['status' => 'completed'], 'Booking completed');

        return back()->with('success', 'Booking marked as completed.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string'],
        ]);

        $booking->update([
            'status'              => 'cancelled',
            'cancelled_by'        => auth()->id(),
            'cancelled_at'        => now(),
            'cancellation_reason' => $request->get('cancellation_reason'),
        ]);

        $linkedUser = \App\Models\User::where('parishioner_id', $booking->parishioner_id)->first();
        if ($linkedUser) {
            $linkedUser->notify(new BookingStatusNotification($booking, 'cancelled'));
        }

        AuditLog::record('cancel', $booking, ['status' => $booking->getOriginal('status')], ['status' => 'cancelled'], 'Booking cancelled');

        return back()->with('success', 'Booking cancelled.');
    }

    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year  = $request->get('year', now()->year);

        $bookings = Booking::whereYear('scheduled_date', $year)
            ->whereMonth('scheduled_date', $month)
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('parishioner')
            ->get()
            ->map(fn($b) => [
                'id'    => $b->id,
                'title' => $b->getTypeLabel() . ' - ' . $b->parishioner->full_name,
                'start' => $b->scheduled_date->format('Y-m-d') . ($b->scheduled_time ? 'T' . $b->scheduled_time : ''),
                'color' => $b->status === 'confirmed' ? '#16a34a' : '#d97706',
                'url'   => route('admin.bookings.show', $b),
            ]);

        return view('admin.bookings.calendar', compact('bookings', 'month', 'year'));
    }

    private function validateBooking(Request $request): array
    {
        return $request->validate([
            'parishioner_id' => ['required', 'exists:parishioners,id'],
            'booking_type'   => ['required', 'string'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'service_fee'    => ['nullable', 'numeric', 'min:0'],
            'address'        => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string'],
        ]);
    }
}
