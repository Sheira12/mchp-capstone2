<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\BookingRequirement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminBookingRequirementNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Booking $booking,
        private BookingRequirement $item
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $parishioner = $this->booking->parishioner?->full_name ?? 'A parishioner';
        $reqName     = $this->item->requirement?->name ?? 'a requirement';

        return [
            'title'      => 'New Requirement Submitted',
            'message'    => "{$parishioner} submitted {$reqName} for {$this->booking->getTypeLabel()} booking.",
            'booking_id' => $this->booking->id,
            'reference'  => $this->booking->reference_number,
            'icon'       => 'document',
            'url'        => url('/admin/booking-requirements/booking/' . $this->booking->id),
        ];
    }
}
