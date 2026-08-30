<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Booking $booking,
        private string $event // 'created', 'confirmed', 'cancelled'
    ) {}

    public function via($notifiable): array
    {
        // Database only — mail is handled separately via sendOtpEmail-style HTTP API
        // Keeping mail here causes 500 when email provider rejects the message
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => $this->getStatusMessage(),
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->reference_number,
            'event' => $this->event,
            'url' => url('/portal/bookings/' . $this->booking->id),
        ];
    }

    public function failed(\Throwable $e): void
    {
        \Illuminate\Support\Facades\Log::warning('BookingStatusNotification failed: ' . $e->getMessage());
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = match ($this->event) {
            'created'   => 'Booking Received - ' . $this->booking->reference_number,
            'confirmed' => 'Booking Confirmed - ' . $this->booking->reference_number,
            'cancelled' => 'Booking Cancelled - ' . $this->booking->reference_number,
            'reminder'  => 'Booking Reminder - ' . $this->booking->reference_number,
            default     => 'Booking Update - ' . $this->booking->reference_number,
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Dear ' . ($notifiable->parishioner?->full_name ?? $notifiable->name) . ',')
            ->line($this->getStatusMessage());

        $message->line('**Service:** ' . $this->booking->getTypeLabel())
                ->line('**Date:** ' . $this->booking->scheduled_date->format('F d, Y'))
                ->line('**Reference:** ' . $this->booking->reference_number);

        if ($this->event === 'confirmed') {
            $message->line('**Fee:** ₱' . number_format($this->booking->service_fee, 2));
            if ($this->booking->admin_notes) {
                $message->line('**Notes from Parish:** ' . $this->booking->admin_notes);
            }
        }

        if ($this->event === 'cancelled' && $this->booking->cancellation_reason) {
            $message->line('**Reason:** ' . $this->booking->cancellation_reason);
        }

        $message->action('View Booking', url('/portal/bookings/' . $this->booking->id))
                ->line('For inquiries, please contact the parish office.')
                ->salutation('God bless, ' . config('parish.name'));

        // Log the email
        try {
            EmailLog::create([
                'to_email'     => $notifiable->email,
                'to_name'      => $notifiable->parishioner?->full_name ?? $notifiable->name,
                'subject'      => $subject,
                'template'     => 'booking_status',
                'status'       => 'sent',
                'sent_at'      => now(),
                'related_type' => Booking::class,
                'related_id'   => $this->booking->id,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('EmailLog creation failed: ' . $e->getMessage());
        }

        return $message;
    }

    private function getStatusMessage(): string
    {
        return match ($this->event) {
            'created'   => 'We have received your booking request. Our parish staff will review and confirm it shortly.',
            'confirmed' => 'Your booking has been confirmed! Please prepare the required documents and arrive on time.',
            'cancelled' => 'We regret to inform you that your booking has been cancelled.',
            'reminder'  => 'This is a friendly reminder that you have a booking scheduled for tomorrow. Please be on time and bring all required documents.',
            default     => 'Your booking status has been updated.',
        };
    }
}
