<?php

namespace App\Notifications;

use App\Models\EmailLog;
use App\Models\Parishioner;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EligibilityNotification extends Notification
{
    use Queueable;

    /**
     * Events:
     *   eligible           – parishioner is now fully eligible to book a service
     *   seminar_registered – registered for an upcoming seminar
     *   seminar_attended   – admin marked attendance
     *   document_approved  – admin approved a submitted document
     *   document_revision  – admin requested revision on a document
     */
    public function __construct(
        private Parishioner $parishioner,
        private string      $event,
        private array       $context = []  // service, seminar, date, venue, rule, etc.
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title'   => $this->getTitle(),
            'message' => $this->getMessage(),
            'event'   => $this->event,
            'icon'    => $this->getIcon(),
            'url'     => $this->getUrl(),
            'context' => $this->context,
        ];
    }

    public function sendEmail(object $notifiable): void
    {
        $email = $notifiable->email ?? null;
        if (!$email) return;

        $name        = $this->parishioner->full_name ?? $notifiable->name ?? 'Parishioner';
        $subject     = $this->getTitle() . ' — ' . config('parish.name', 'MHCP');
        $fromAddress = config('mail.from.address', 'noreply@mhcparish.ph');
        $fromName    = config('mail.from.name', 'Mary Help of Christians Parish');
        $html        = $this->buildHtml($name);

        // Try Brevo
        $brevoKey = env('BREVO_API_KEY');
        if ($brevoKey) {
            try {
                $resp = Http::withHeaders(['api-key' => $brevoKey, 'Content-Type' => 'application/json'])
                    ->timeout(15)->post('https://api.brevo.com/v3/smtp/email', [
                        'sender'      => ['name' => $fromName, 'email' => $fromAddress],
                        'to'          => [['email' => $email, 'name' => $name]],
                        'subject'     => $subject,
                        'htmlContent' => $html,
                    ]);
                if ($resp->successful()) { $this->logEmail($email, $name, $subject, 'sent'); return; }
            } catch (\Exception $e) { Log::warning('EligibilityNotification Brevo: ' . $e->getMessage()); }
        }

        // Try Resend
        $resendKey = env('RESEND_API_KEY');
        if ($resendKey && !str_contains($resendKey, 'RENDER_VAR')) {
            try {
                $resp = Http::withToken($resendKey)->timeout(15)->post('https://api.resend.com/emails', [
                    'from' => "$fromName <$fromAddress>", 'to' => [$email],
                    'subject' => $subject, 'html' => $html,
                ]);
                if ($resp->successful()) { $this->logEmail($email, $name, $subject, 'sent'); return; }
            } catch (\Exception $e) { Log::warning('EligibilityNotification Resend: ' . $e->getMessage()); }
        }

        $this->logEmail($email, $name, $subject, 'failed');
    }

    private function getTitle(): string
    {
        return match ($this->event) {
            'eligible'           => '🎉 You are now eligible to book ' . ($this->context['service'] ?? 'a service') . '!',
            'seminar_registered' => '✅ Seminar Registration Confirmed',
            'seminar_attended'   => '🏆 Seminar Attendance Recorded',
            'document_approved'  => '✅ Document Approved',
            'document_revision'  => '⚠️ Document Needs Revision',
            default              => 'Eligibility Update',
        };
    }

    private function getMessage(): string
    {
        return match ($this->event) {
            'eligible' => 'All eligibility requirements for ' . ($this->context['service'] ?? 'the service') . ' have been satisfied. You may now proceed to book.',
            'seminar_registered' => 'You have been registered for "' . ($this->context['seminar'] ?? 'the seminar') . '" on ' . ($this->context['date'] ?? '') . ($this->context['venue'] ? ' at ' . $this->context['venue'] : '') . '.',
            'seminar_attended'   => 'Your attendance at "' . ($this->context['seminar'] ?? 'the seminar') . '" has been recorded. This satisfies a seminar requirement.',
            'document_approved'  => 'Your submitted document has been approved by the parish office.',
            'document_revision'  => 'Your submitted document requires revision. Please review and resubmit.',
            default              => 'Your eligibility status has been updated.',
        };
    }

    private function getIcon(): string
    {
        return match ($this->event) {
            'eligible'           => 'check',
            'seminar_registered' => 'calendar',
            'seminar_attended'   => 'academic',
            'document_approved'  => 'check',
            'document_revision'  => 'warning',
            default              => 'info',
        };
    }

    private function getUrl(): string
    {
        return match ($this->event) {
            'eligible'           => url('/portal/eligibility'),
            'seminar_registered',
            'seminar_attended'   => url('/portal/seminars/history'),
            'document_approved',
            'document_revision'  => url('/portal/eligibility'),
            default              => url('/portal/dashboard'),
        };
    }

    private function buildHtml(string $name): string
    {
        $parish  = config('parish.name', 'Mary Help of Christians Parish');
        $title   = $this->getTitle();
        $message = $this->getMessage();
        $url     = $this->getUrl();
        $isAlert = $this->event === 'document_revision';
        $hdrBg   = $isAlert ? '#dc2626' : '#1e3a8a';
        $btnBg   = $isAlert ? '#dc2626' : '#2563eb';

        return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:32px 16px;">
<tr><td align="center">
<table width="540" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.10);">
<tr><td style="background:{$hdrBg};padding:24px 40px;text-align:center;">
  <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#bfdbfe;">{$parish}</p>
  <h1 style="margin:8px 0 0;font-size:18px;font-weight:800;color:#fff;">{$title}</h1>
</td></tr>
<tr><td style="padding:28px 40px;">
  <p style="font-size:15px;font-weight:600;color:#0f172a;">Hello, {$name}!</p>
  <p style="font-size:14px;color:#475569;line-height:1.7;">{$message}</p>
  <p style="text-align:center;margin-top:20px;">
    <a href="{$url}" style="display:inline-block;background:{$btnBg};color:#fff;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;">
      View in Portal
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
                'to_email' => $email, 'to_name' => $name,
                'subject'  => $subject, 'template' => 'eligibility_' . $this->event,
                'status'   => $status, 'sent_at'  => $status === 'sent' ? now() : null,
            ]);
        } catch (\Exception $e) { Log::warning('EmailLog failed: ' . $e->getMessage()); }
    }
}
