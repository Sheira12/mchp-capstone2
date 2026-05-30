<?php

namespace App\Notifications;

use App\Models\EmailLog;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceiptNotification extends Notification
{
    use Queueable;

    public function __construct(private Payment $payment) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Support both User and Parishioner notifiables
        $name  = $notifiable->parishioner?->full_name ?? $notifiable->full_name ?? $notifiable->name ?? 'Parishioner';
        $email = $notifiable->email;

        $subject = 'Payment Receipt — ' . $this->payment->receipt_number;

        $methodLabels = [
            'gcash' => 'GCash',
            'maya'  => 'Maya (PayMaya)',
            'cash'  => 'Cash',
            'bank'  => 'Bank Transfer',
        ];

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Dear ' . $name . ',')
            ->line('✅ **Your payment has been successfully received!**')
            ->line('---')
            ->line('**Receipt Number:** ' . $this->payment->receipt_number)
            ->line('**Amount Paid:** ₱' . number_format($this->payment->amount, 2))
            ->line('**Payment Method:** ' . ($methodLabels[$this->payment->payment_method] ?? ucfirst($this->payment->payment_method)))
            ->line('**Date & Time:** ' . ($this->payment->paid_at?->format('F d, Y g:i A') ?? now()->format('F d, Y g:i A')));

        if ($this->payment->booking) {
            $message->line('---')
                    ->line('**Service:** ' . $this->payment->booking->getTypeLabel())
                    ->line('**Scheduled Date:** ' . $this->payment->booking->scheduled_date->format('F d, Y'))
                    ->line('**Booking Reference:** ' . $this->payment->booking->reference_number);
        }

        $message->action('View Receipt Online', url('/portal/payments/receipt/' . $this->payment->id))
                ->line('Please keep this receipt for your records.')
                ->line('If you have any questions, please contact the parish office.')
                ->salutation('God bless, ' . PHP_EOL . config('parish.name'));

        // Log the email
        try {
            EmailLog::create([
                'to_email'     => $email,
                'to_name'      => $name,
                'subject'      => $subject,
                'template'     => 'payment_receipt',
                'status'       => 'sent',
                'sent_at'      => now(),
                'related_type' => Payment::class,
                'related_id'   => $this->payment->id,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('EmailLog creation failed: ' . $e->getMessage());
        }

        return $message;
    }
}
