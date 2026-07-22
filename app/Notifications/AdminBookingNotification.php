<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Database notification sent to ALL admin users when a parishioner
 * submits a new booking. Shows in the admin notification bell.
 */
class AdminBookingNotification extends Notification
{
    use Queueable;

    public function __construct(private Booking $booking) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'booking_id'       => $this->booking->id,
            'reference_number' => $this->booking->reference_number,
            'parishioner_name' => $this->booking->parishioner->full_name,
            'service'          => $this->booking->getTypeLabel(),
            'scheduled_date'   => $this->booking->scheduled_date->format('M d, Y'),
            'scheduled_time'   => $this->booking->scheduled_time
                ? \Carbon\Carbon::parse($this->booking->scheduled_time)->format('g:i A')
                : null,
            'message'          => $this->booking->parishioner->full_name
                . ' submitted a new booking for '
                . $this->booking->getTypeLabel()
                . ' on '
                . $this->booking->scheduled_date->format('M d, Y') . '.',
            'url'              => route('admin.bookings.show', $this->booking->id),
            'type'             => 'new_booking',
        ];
    }
}
