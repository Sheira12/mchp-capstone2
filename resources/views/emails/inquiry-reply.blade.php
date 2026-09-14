<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:32px 16px;">
<tr><td align="center">
<table width="540" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.10);">

  {{-- Header --}}
  <tr><td style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%);padding:28px 40px;text-align:center;">
    <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#bfdbfe;">{{ config('parish.name') }}</p>
    <h1 style="margin:8px 0 0;font-size:20px;font-weight:800;color:#ffffff;">Inquiry Reply</h1>
  </td></tr>

  {{-- Body --}}
  <tr><td style="padding:32px 40px;">
    <p style="font-size:15px;font-weight:600;color:#0f172a;">Dear {{ $inquiry->name }},</p>
    <p style="font-size:14px;color:#475569;line-height:1.7;">
      Thank you for reaching out to us. Here is our reply to your inquiry:
    </p>

    {{-- Original inquiry block --}}
    <div style="background:#f8fafc;border-left:4px solid #2563eb;border-radius:4px;padding:12px 16px;margin:16px 0;">
      <p style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:1px;margin:0 0 4px;">
        Your Inquiry: {{ $inquiry->subject }}
      </p>
      @if($inquiry->preferred_date)
      <p style="font-size:12px;color:#64748b;margin:0 0 4px;">
        <strong>Preferred Date:</strong> {{ $inquiry->preferred_date->format('F d, Y') }}
        @if($inquiry->preferred_time) &nbsp;·&nbsp; <strong>Time:</strong> {{ $inquiry->preferred_time }} @endif
      </p>
      @endif
      <p style="font-size:13px;color:#475569;margin:0;line-height:1.6;">{{ $inquiry->message }}</p>
      @if($inquiry->attachments && count($inquiry->attachments))
      <p style="font-size:12px;color:#64748b;margin:8px 0 0;">
        <strong>Submitted files:</strong>
        @foreach($inquiry->attachments as $a) {{ $a['original_name'] }}{{ !$loop->last ? ', ' : '' }} @endforeach
      </p>
      @endif
    </div>

    {{-- Admin reply block --}}
    <div style="background:#eff6ff;border-left:4px solid #1d4ed8;border-radius:4px;padding:16px;margin:16px 0;">
      <p style="font-size:11px;font-weight:700;color:#1d4ed8;text-transform:uppercase;letter-spacing:1px;margin:0 0 8px;">
        Reply from {{ $adminName }}:
      </p>
      <p style="font-size:14px;color:#1e3a8a;line-height:1.7;margin:0;">{{ $replyMessage }}</p>
    </div>

    {{-- Admin attachments list --}}
    @if($adminAttachments && count($adminAttachments))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin:16px 0;">
      <p style="font-size:12px;font-weight:700;color:#166534;margin:0 0 6px;">
        📎 Files attached to this reply:
      </p>
      @foreach($adminAttachments as $a)
      <p style="font-size:13px;color:#15803d;margin:2px 0;">
        • {{ $a['original_name'] }} ({{ round($a['size'] / 1024, 1) }} KB)
      </p>
      @endforeach
      <p style="font-size:11px;color:#4ade80;margin:6px 0 0;">Files are attached to this email.</p>
    </div>
    @endif

    <p style="font-size:12px;color:#94a3b8;margin-top:24px;border-top:1px solid #e2e8f0;padding-top:16px;">
      If you have further questions, please don't hesitate to contact us again through our website.<br>
      God bless,<br><strong>{{ config('parish.name') }}</strong><br>
      {{ config('parish.address') }}
    </p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
