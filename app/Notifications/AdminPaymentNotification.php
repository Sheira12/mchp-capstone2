<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to all admin users when PayMongo confirms a GCash/Maya payment.
 *
 * Workflow context:
 *   PayMongo confirmed the charge → payment is still 'pending' (not yet 'paid').
 *   Admin must review and click "Approve & Mark as Paid" to finalize.
 *
 * Channels:
 *   database → admin bell notification
 *   mail     → email to each admin (if they have an email address)
 */
class AdminPaymentNotification extends Notification
{
    use Queueable;

    public function __construct(private Payment $payment) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        $parishioner = $this->payment->parishioner;
        $name        = $parishioner?->full_name ?? 'Unknown Parishioner';
        $method      = Payment::METHODS[$this->payment->payment_method] ?? ucfirst($this->payment->payment_method);

        return [
            'payment_id'       => $this->payment->id,
            'reference_number' => $this->payment->reference_number,
            'parishioner_name' => $name,
            'amount'           => $this->payment->amount,
            'payment_method'   => $method,
            'booking_id'       => $this->payment->booking_id,
            'booking_ref'      => $this->payment->booking?->reference_number,
            'service'          => $this->payment->booking?->getTypeLabel(),
            'message'          => $name . ' submitted a ' . $method
                . ' payment of ₱' . number_format($this->payment->amount, 2)
                . '. Please verify and approve.',
            'url'              => route('admin.payments.show', $this->payment->id),
            'type'             => 'payment_pending_verification',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $parishioner = $this->payment->parishioner;
        $name        = $parishioner?->full_name ?? 'Unknown Parishioner';
        $method      = Payment::METHODS[$this->payment->payment_method] ?? ucfirst($this->payment->payment_method);
        $submittedAt = $this->payment->created_at->format('F d, Y g:i A');

        return (new MailMessage)
            ->subject('⏳ Payment Awaiting Verification — ' . $this->payment->reference_number)
            ->greeting('Hello,')
            ->line('**A new payment requires your verification.**')
            ->line('---')
            ->line('**Parishioner:** ' . $name)
            ->line('**Amount:** ₱' . number_format($this->payment->amount, 2))
            ->line('**Payment Method:** ' . $method)
            ->line('**Reference:** ' . $this->payment->reference_number)
            ->line('**Submitted:** ' . $submittedAt)
            ->when($this->payment->submitted_reference, fn ($m) =>
                $m->line('**Parishioner Reference:** ' . $this->payment->submitted_reference)
            )
            ->when($this->payment->booking, fn ($m) =>
                $m->line('---')
                  ->line('**Service:** ' . $this->payment->booking->getTypeLabel())
                  ->line('**Booking Reference:** ' . $this->payment->booking->reference_number)
            )
            ->line('---')
            ->line('PayMongo has confirmed the charge. Please verify the payment reference and approve.')
            ->action('Review & Approve Payment', route('admin.payments.show', $this->payment->id))
            ->salutation('— ' . config('parish.name'));
    }
}
