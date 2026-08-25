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
            $query->where(function ($q) use ($search) {
                $q->whereHas('parishioner', fn($sq) => $sq->search($search))
                  ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $bookings = $query->orderBy('scheduled_date')->orderBy('scheduled_time')
            ->paginate(20)
            ->withQueryString();

        $pendingCount = Booking::pending()->count();

        return view('admin.bookings.index', compact('bookings', 'pendingCount'));
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
            // Portal DB notification
            $linkedUser->notify(new \App\Notifications\ParishionerStatusNotification(
                'Booking Confirmed ✓',
                'Your booking for ' . $booking->getTypeLabel() . ' on ' . $booking->scheduled_date->format('M d, Y') . ' has been confirmed.',
                route('parishioner.bookings.show', $booking->id),
                'check'
            ));
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
            // Portal DB notification
            $linkedUser->notify(new \App\Notifications\ParishionerStatusNotification(
                'Booking Cancelled',
                'Your booking for ' . $booking->getTypeLabel() . ' on ' . $booking->scheduled_date->format('M d, Y') . ' has been cancelled.',
                route('parishioner.bookings.index'),
                'bell'
            ));
        }

        AuditLog::record('cancel', $booking, ['status' => $booking->getOriginal('status')], ['status' => 'cancelled'], 'Booking cancelled');

        return back()->with('success', 'Booking cancelled.');
    }

    /**
     * QR Scanner page — camera-based QR scanning for walk-in verification.
     */
    public function qrScanner()
    {
        return view('admin.bookings.qr-scanner');
    }

    /**
     * API endpoint called by the QR scanner JS to look up a booking by token.
     */
    public function qrVerify(Request $request)
    {
        $request->validate(['token' => ['required', 'string']]);

        $qrCode = \App\Models\QrCode::where('token', $request->get('token'))
            ->where('is_active', true)
            ->first();

        if (!$qrCode) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired QR code.'], 404);
        }

        $entity = $qrCode->qrCodeable;

        if (!$entity || !($entity instanceof Booking)) {
            return response()->json(['valid' => false, 'message' => 'QR code is not linked to a booking.'], 404);
        }

        $entity->load(['parishioner', 'payment']);
        $qrCode->incrementScanCount();

        return response()->json([
            'valid'   => true,
            'booking' => [
                'id'             => $entity->id,
                'reference'      => $entity->reference_number,
                'type'           => $entity->getTypeLabel(),
                'status'         => $entity->status,
                'status_label'   => $entity->getStatusLabel(),
                'parishioner'    => $entity->parishioner->full_name,
                'contact'        => $entity->parishioner->contact_number,
                'scheduled_date' => $entity->scheduled_date->format('F d, Y'),
                'scheduled_time' => $entity->scheduled_time
                    ? \Carbon\Carbon::parse($entity->scheduled_time)->format('g:i A')
                    : 'TBD',
                'service_fee'    => $entity->service_fee,
                'payment_status' => $entity->payment?->status ?? 'unpaid',
                'url'            => route('admin.bookings.show', $entity),
            ],
        ]);
    }

    /**
     * Print walk-in stub — printable QR slip for the parishioner.
     */
    public function printStub(Booking $booking)
    {
        $booking->load(['parishioner', 'qrCode']);

        // Generate QR if missing
        if (!$booking->qrCode) {
            app(\App\Services\QrCodeService::class)->generateForBooking($booking);
            $booking->refresh();
        }

        // Build base64 QR for the stub
        $qrBase64 = null;
        if ($booking->qrCode?->qr_image_path) {
            $svg = \Illuminate\Support\Facades\Storage::disk('public')
                ->get($booking->qrCode->qr_image_path);
            if ($svg) {
                $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
            }
        }

        return view('admin.bookings.stub', compact('booking', 'qrBase64'));
    }

    public function calendar(Request $request)    {
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

    public function edit(Booking $booking)
    {
        $booking->load('parishioner');
        return view('admin.bookings.edit', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'booking_type'   => ['required', 'string'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['nullable', 'date_format:H:i'],
            'service_fee'    => ['nullable', 'numeric', 'min:0'],
            'address'        => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string'],
            'admin_notes'    => ['nullable', 'string'],
            'status'         => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        $oldValues = $booking->toArray();
        $booking->update($validated);
        AuditLog::record('update', $booking, $oldValues, $booking->fresh()->toArray(), 'Booking updated');

        return redirect()->route('admin.bookings.show', $booking)->with('success', 'Booking updated.');
    }

    public function destroy(Booking $booking)
    {
        AuditLog::record('delete', $booking, $booking->toArray(), [], 'Booking deleted');
        $booking->delete();
        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted.');
    }
}
