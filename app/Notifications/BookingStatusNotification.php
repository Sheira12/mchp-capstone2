<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookingStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Booking $booking,
        private string $event // 'created', 'confirmed', 'cancelled', 'reminder'
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message'    => $this->getStatusMessage(),
            'booking_id' => $this->booking->id,
            'reference'  => $this->booking->reference_number,
            'event'      => $this->event,
            'url'        => url('/portal/bookings/' . $this->booking->id),
        ];
    }

    /**
     * Send booking email via Brevo or Resend HTTP API.
     * Call this AFTER the notification is dispatched.
     * Email failure NEVER affects the booking transaction.
     */
    public function sendEmail(object $notifiable): void
    {
        $email = $notifiable->email ?? null;
        if (!$email) return;

        $name        = $notifiable->parishioner?->full_name ?? $notifiable->name ?? 'Parishioner';
        $subject     = $this->getEmailSubject();
        $fromAddress = config('mail.from.address', 'noreply@mhcparish.ph');
        $fromName    = config('mail.from.name', 'Mary Help of Christians Parish');

        $html = $this->buildEmailHtml($name);

        // Try Brevo HTTP API first (no domain verification needed for verified sender)
        $brevoKey = env('BREVO_API_KEY');
        if ($brevoKey) {
            try {
                $response = Http::withHeaders([
                    'api-key'      => $brevoKey,
                    'Content-Type' => 'application/json',
                ])->timeout(15)->post('https://api.brevo.com/v3/smtp/email', [
                    'sender'      => ['name' => $fromName, 'email' => $fromAddress],
                    'to'          => [['email' => $email, 'name' => $name]],
                    'subject'     => $subject,
                    'htmlContent' => $html,
                ]);

                if ($response->successful()) {
                    Log::info('Booking email sent via Brevo', [
                        'booking_id' => $this->booking->id,
                        'event'      => $this->event,
                        'recipient'  => substr($email, 0, 3) . '***@' . explode('@', $email)[1],
                    ]);
                    $this->logEmail($email, $name, $subject, 'sent');
                    return;
                }

                Log::error('Booking email Brevo error', [
                    'booking_id' => $this->booking->id,
                    'status'     => $response->status(),
                    'code'       => $response->json('code') ?? 'n/a',
                    'message'    => $response->json('message') ?? 'n/a',
                ]);
            } catch (\Exception $e) {
                Log::error('Booking email Brevo exception', [
                    'booking_id' => $this->booking->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        // Fallback: Resend HTTP API
        $resendKey = env('RESEND_API_KEY');
        if ($resendKey && $resendKey !== 'RENDER_VAR_OVERRIDE') {
            try {
                $response = Http::withToken($resendKey)
                    ->timeout(15)
                    ->post('https://api.resend.com/emails', [
                        'from'    => "$fromName <$fromAddress>",
                        'to'      => [$email],
                        'subject' => $subject,
                        'html'    => $html,
                    ]);

                if ($response->successful()) {
                    Log::info('Booking email sent via Resend', [
                        'booking_id' => $this->booking->id,
                        'event'      => $this->event,
                    ]);
                    $this->logEmail($email, $name, $subject, 'sent');
                    return;
                }

                Log::error('Booking email Resend error', [
                    'booking_id' => $this->booking->id,
                    'status'     => $response->status(),
                    'message'    => $response->json('message') ?? 'n/a',
                ]);
            } catch (\Exception $e) {
                Log::error('Booking email Resend exception', [
                    'booking_id' => $this->booking->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        Log::warning('Booking email: no working provider available', [
            'booking_id' => $this->booking->id,
            'event'      => $this->event,
        ]);
        $this->logEmail($email, $name, $subject, 'failed');
    }

    private function getEmailSubject(): string
    {
        return match ($this->event) {
            'created'   => 'Booking Received — ' . $this->booking->reference_number,
            'confirmed' => 'Booking Confirmed — ' . $this->booking->reference_number,
            'cancelled' => 'Booking Cancelled — ' . $this->booking->reference_number,
            'reminder'  => 'Booking Reminder — ' . $this->booking->reference_number,
            default     => 'Booking Update — ' . $this->booking->reference_number,
        };
    }

    private function buildEmailHtml(string $name): string
    {
        $statusMsg  = $this->getStatusMessage();
        $parish     = config('parish.name', 'Mary Help of Christians Parish');
        $service    = $this->booking->getTypeLabel();
        $date       = $this->booking->scheduled_date->format('F d, Y');
        $reference  = $this->booking->reference_number;
        $portalUrl  = url('/portal/bookings/' . $this->booking->id);

        $extraLines = '';
        if ($this->event === 'confirmed') {
            $fee = '₱' . number_format($this->booking->service_fee, 2);
            $extraLines .= "<p style='margin:4px 0;font-size:14px;color:#475569;'><strong>Fee:</strong> $fee</p>";
            if ($this->booking->admin_notes) {
                $extraLines .= "<p style='margin:4px 0;font-size:14px;color:#475569;'><strong>Notes:</strong> {$this->booking->admin_notes}</p>";
            }
        }
        if ($this->event === 'cancelled' && $this->booking->cancellation_reason) {
            $extraLines .= "<p style='margin:4px 0;font-size:14px;color:#475569;'><strong>Reason:</strong> {$this->booking->cancellation_reason}</p>";
        }

        return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:32px 16px;">
<tr><td align="center">
<table width="540" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.10);">
<tr><td style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%);padding:28px 40px;text-align:center;">
  <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#bfdbfe;">$parish</p>
  <h1 style="margin:8px 0 0;font-size:20px;font-weight:800;color:#ffffff;">Booking Notification</h1>
</td></tr>
<tr><td style="padding:32px 40px;">
  <p style="font-size:15px;font-weight:600;color:#0f172a;">Hello, $name!</p>
  <p style="font-size:14px;color:#475569;line-height:1.7;">$statusMsg</p>
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:16px 0;">
    <tr><td style="padding:16px;">
      <p style="margin:4px 0;font-size:14px;color:#475569;"><strong>Service:</strong> $service</p>
      <p style="margin:4px 0;font-size:14px;color:#475569;"><strong>Date:</strong> $date</p>
      <p style="margin:4px 0;font-size:14px;color:#475569;"><strong>Reference:</strong> $reference</p>
      $extraLines
    </td></tr>
  </table>
  <p style="text-align:center;margin-top:24px;">
    <a href="$portalUrl" style="display:inline-block;background:#2563eb;color:#fff;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;">View Booking</a>
  </p>
  <p style="font-size:12px;color:#94a3b8;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px;">
    For inquiries, contact the parish office. God bless,<br>$parish
  </p>
</td></tr>
</table>
</td></tr>
</table>
</body></html>
HTML;
    }

    private function logEmail(string $email, string $name, string $subject, string $status): void
    {
        try {
            EmailLog::create([
                'to_email'     => $email,
                'to_name'      => $name,
                'subject'      => $subject,
                'template'     => 'booking_' . $this->event,
                'status'       => $status,
                'sent_at'      => $status === 'sent' ? now() : null,
                'related_type' => Booking::class,
                'related_id'   => $this->booking->id,
            ]);
        } catch (\Exception $e) {
            Log::warning('EmailLog creation failed: ' . $e->getMessage());
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::warning('BookingStatusNotification dispatch failed: ' . $e->getMessage());
    }

    private function getStatusMessage(): string
    {
        return match ($this->event) {
            'created'   => 'We have received your booking request. Our parish staff will review and confirm it shortly.',
            'confirmed' => 'Your booking has been confirmed! Please prepare the required documents and arrive on time.',
            'cancelled' => 'We regret to inform you that your booking has been cancelled.',
            'reminder'  => 'This is a friendly reminder that you have a booking scheduled for tomorrow. Please be on time.',
            default     => 'Your booking status has been updated.',
        };
    }
}
