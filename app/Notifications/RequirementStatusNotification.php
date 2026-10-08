<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\BookingRequirement;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RequirementStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Booking $booking,
        private string $event,                      // all_approved | item_approved | needs_revision
        private ?BookingRequirement $item = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'      => $this->getTitle(),
            'message'    => $this->getMessage(),
            'booking_id' => $this->booking->id,
            'reference'  => $this->booking->reference_number,
            'event'      => $this->event,
            'icon'       => $this->event === 'needs_revision' ? 'warning' : 'check',
            'url'        => url('/portal/bookings/' . $this->booking->id . '/requirements'),
        ];
    }

    public function sendEmail(object $notifiable): void
    {
        $email = $notifiable->email ?? null;
        if (!$email) return;

        $name        = $notifiable->parishioner?->full_name ?? $notifiable->name ?? 'Parishioner';
        $subject     = $this->getEmailSubject();
        $fromAddress = config('mail.from.address', 'noreply@mhcparish.ph');
        $fromName    = config('mail.from.name', 'Mary Help of Christians Parish');
        $html        = $this->buildEmailHtml($name);

        // Try Brevo first, then Resend
        $brevoKey = env('BREVO_API_KEY');
        if ($brevoKey) {
            try {
                $resp = Http::withHeaders(['api-key' => $brevoKey, 'Content-Type' => 'application/json'])
                    ->timeout(15)
                    ->post('https://api.brevo.com/v3/smtp/email', [
                        'sender'      => ['name' => $fromName, 'email' => $fromAddress],
                        'to'          => [['email' => $email, 'name' => $name]],
                        'subject'     => $subject,
                        'htmlContent' => $html,
                    ]);
                if ($resp->successful()) {
                    $this->logEmail($email, $name, $subject, 'sent');
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('Requirement email Brevo failed: ' . $e->getMessage());
            }
        }

        $resendKey = env('RESEND_API_KEY');
        if ($resendKey && !str_contains($resendKey, 'RENDER_VAR')) {
            try {
                $resp = Http::withToken($resendKey)->timeout(15)->post('https://api.resend.com/emails', [
                    'from' => "$fromName <$fromAddress>", 'to' => [$email],
                    'subject' => $subject, 'html' => $html,
                ]);
                if ($resp->successful()) {
                    $this->logEmail($email, $name, $subject, 'sent');
                    return;
                }
            } catch (\Exception $e) {
                Log::warning('Requirement email Resend failed: ' . $e->getMessage());
            }
        }

        $this->logEmail($email, $name, $subject, 'failed');
    }

    private function getTitle(): string
    {
        return match ($this->event) {
            'all_approved'  => '✅ All Requirements Approved — You Can Now Book!',
            'item_approved' => '✅ Requirement Approved',
            'needs_revision'=> '⚠️ Requirement Needs Revision',
            default         => 'Requirement Update',
        };
    }

    private function getMessage(): string
    {
        $service = $this->booking->getTypeLabel();
        return match ($this->event) {
            'all_approved'  => "All required documents for your {$service} booking have been approved. You can now select a date and finalize your booking.",
            'item_approved' => 'One of your submitted requirements has been approved. Check your booking for full progress.',
            'needs_revision'=> 'One of your requirements needs revision: ' . ($this->item?->requirement?->name ?? 'See booking for details') . '. Remark: ' . ($this->item?->admin_remark ?? '—'),
            default         => 'Your booking requirement status has been updated.',
        };
    }

    private function getEmailSubject(): string
    {
        return match ($this->event) {
            'all_approved'  => 'All Requirements Approved — ' . $this->booking->reference_number,
            'item_approved' => 'Requirement Approved — ' . $this->booking->reference_number,
            'needs_revision'=> 'Action Required: Requirement Revision — ' . $this->booking->reference_number,
            default         => 'Requirement Update — ' . $this->booking->reference_number,
        };
    }

    private function buildEmailHtml(string $name): string
    {
        $parish   = config('parish.name', 'Mary Help of Christians Parish');
        $message  = $this->getMessage();
        $service  = $this->booking->getTypeLabel();
        $ref      = $this->booking->reference_number;
        $url      = url('/portal/bookings/' . $this->booking->id . '/requirements');
        $isRevision = $this->event === 'needs_revision';
        $headerBg = $isRevision ? '#dc2626' : '#16a34a';
        $btnBg    = $isRevision ? '#dc2626' : '#2563eb';
        $icon     = $isRevision ? '⚠️' : '✅';

        $remarkRow = '';
        if ($isRevision && $this->item?->admin_remark) {
            $remark    = htmlspecialchars($this->item->admin_remark);
            $reqName   = htmlspecialchars($this->item->requirement?->name ?? '');
            $remarkRow = "<tr><td style='padding:16px;'>
                <p style='margin:4px 0;font-size:13px;color:#991b1b;background:#fee2e2;border:1px solid #fca5a5;border-radius:6px;padding:8px 12px;'>
                    <strong>Requirement:</strong> {$reqName}<br>
                    <strong>Admin Note:</strong> {$remark}
                </p>
            </td></tr>";
        }

        return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:32px 16px;">
<tr><td align="center">
<table width="540" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.10);">
<tr><td style="background:{$headerBg};padding:24px 40px;text-align:center;">
  <p style="margin:0;font-size:28px;">{$icon}</p>
  <h1 style="margin:8px 0 0;font-size:18px;font-weight:800;color:#fff;">{$this->getTitle()}</h1>
</td></tr>
<tr><td style="padding:28px 40px;">
  <p style="font-size:15px;font-weight:600;color:#0f172a;">Hello, {$name}!</p>
  <p style="font-size:14px;color:#475569;line-height:1.7;">{$message}</p>
  <table width="100%" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin:16px 0;">
    <tr><td style="padding:16px;">
      <p style="margin:4px 0;font-size:14px;color:#475569;"><strong>Service:</strong> {$service}</p>
      <p style="margin:4px 0;font-size:14px;color:#475569;"><strong>Reference:</strong> {$ref}</p>
    </td></tr>
    {$remarkRow}
  </table>
  <p style="text-align:center;margin-top:20px;">
    <a href="{$url}" style="display:inline-block;background:{$btnBg};color:#fff;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;">
      View Requirements
    </a>
  </p>
  <p style="font-size:12px;color:#94a3b8;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px;">
    God bless,<br>{$parish}
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
                'template'     => 'requirement_' . $this->event,
                'status'       => $status,
                'sent_at'      => $status === 'sent' ? now() : null,
                'related_type' => Booking::class,
                'related_id'   => $this->booking->id,
            ]);
        } catch (\Exception $e) {
            Log::warning('EmailLog failed: ' . $e->getMessage());
        }
    }
}
