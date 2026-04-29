<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingStatusNotification;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature   = 'parish:send-reminders';
    protected $description = 'Send booking reminder emails 1 day before scheduled date';

    public function handle(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $bookings = Booking::where('scheduled_date', $tomorrow)
            ->whereIn('status', ['confirmed'])
            ->where('reminder_sent', false)
            ->with('parishioner')
            ->get();

        foreach ($bookings as $booking) {
            if ($booking->parishioner->email) {
                $booking->parishioner->notify(new BookingStatusNotification($booking, 'reminder'));
                $booking->update(['reminder_sent' => true]);
                $this->info("Reminder sent for booking {$booking->reference_number}");
            }
        }

        $this->info("Processed {$bookings->count()} reminders.");
    }
}
