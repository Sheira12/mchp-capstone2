<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the parishioner when an admin rejects their payment proof.
 * Tells them to resubmit with the correct reference number / proof.
 */
class PaymentRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private Payment $payment) {}

    public function via($notifiable): array
    {
        // Database bell + email
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'payment_id'       => $this->payment->id,
            'reference_number' => $this->payment->reference_number,
            'amount'           => $this->payment->amount,
            'rejection_reason' => $this->payment->rejection_reason,
            'message'          => 'Your payment of ₱' . number_format($this->payment->amount, 2)
                . ' was not verified. Reason: ' . $this->payment->rejection_reason,
            'url'              => route('parishioner.payments.pay', $this->payment->booking_id),
            'type'             => 'payment_rejected',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $name   = $notifiable->parishioner?->full_name ?? $notifiable->name ?? 'Parishioner';
        $method = Payment::METHODS[$this->payment->payment_method] ?? ucfirst($this->payment->payment_method);

        return (new MailMessage)
            ->subject('Payment Not Verified — Action Required')
            ->greeting('Dear ' . $name . ',')
            ->line('Unfortunately, your **' . $method . '** payment of **₱' . number_format($this->payment->amount, 2) . '** could not be verified.')
            ->line('**Reason:** ' . ($this->payment->rejection_reason ?? 'Reference number could not be confirmed.'))
            ->line('---')
            ->line('**Reference:** ' . $this->payment->reference_number)
            ->when($this->payment->booking, fn ($m) =>
                $m->line('**Booking:** ' . $this->payment->booking->getTypeLabel()
                    . ' — ' . $this->payment->booking->reference_number)
            )
            ->line('---')
            ->line('Please resubmit your payment with the correct reference number and screenshot.')
            ->action('Resubmit Payment', $this->payment->booking_id
                ? route('parishioner.payments.pay', $this->payment->booking_id)
                : route('parishioner.bookings.index')
            )
            ->line('If you believe this is an error, please contact the parish office.')
            ->salutation('God bless, ' . PHP_EOL . config('parish.name'));
    }
}
