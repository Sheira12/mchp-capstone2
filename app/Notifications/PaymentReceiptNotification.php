<?php

namespace App\Notifications;

use App\Models\EmailLog;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Payment $payment) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = 'Payment Receipt - ' . $this->payment->receipt_number;

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Dear ' . $notifiable->full_name . ',')
            ->line('Thank you! Your payment has been received.')
            ->line('**Receipt Number:** ' . $this->payment->receipt_number)
            ->line('**Amount Paid:** ₱' . number_format($this->payment->amount, 2))
            ->line('**Payment Method:** ' . (Payment::METHODS[$this->payment->payment_method] ?? $this->payment->payment_method))
            ->line('**Date:** ' . $this->payment->paid_at?->format('F d, Y g:i A'));

        if ($this->payment->booking) {
            $message->line('**Service:** ' . $this->payment->booking->getTypeLabel())
                    ->line('**Booking Reference:** ' . $this->payment->booking->reference_number);
        }

        $message->action('View Payment History', url('/portal/payments'))
                ->line('Please keep this receipt for your records.')
                ->salutation('God bless, ' . config('parish.name'));

        EmailLog::create([
            'to_email'     => $notifiable->email,
            'to_name'      => $notifiable->full_name,
            'subject'      => $subject,
            'template'     => 'payment_receipt',
            'status'       => 'sent',
            'sent_at'      => now(),
            'related_type' => Payment::class,
            'related_id'   => $this->payment->id,
        ]);

        return $message;
    }
}
