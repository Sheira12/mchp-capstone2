<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Login Code — {{ config('parish.name') }}</title>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f4f8;padding:32px 16px;">
  <tr><td align="center">
    <table width="540" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.10);">

      {{-- Header --}}
      <tr>
        <td style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%);padding:28px 40px;text-align:center;">
          <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#bfdbfe;">
            {{ strtoupper(config('parish.name')) }}
          </p>
          <h1 style="margin:0;font-size:20px;font-weight:800;color:#ffffff;">
            Login Verification Code
          </h1>
          <p style="margin:6px 0 0;font-size:12px;color:#93c5fd;">
            Southville 1, Niugan, Cabuyao, Laguna
          </p>
        </td>
      </tr>

      {{-- Body --}}
      <tr>
        <td style="padding:32px 40px;">
          <p style="margin:0 0 16px;font-size:15px;font-weight:600;color:#0f172a;">
            Hello, {{ $user->name }}!
          </p>
          <p style="margin:0 0 24px;font-size:14px;color:#475569;line-height:1.7;">
            A sign-in was requested for your <strong>{{ config('app.name') }}</strong> account.
            Use the verification code below to complete your login.
            This code expires in <strong>5 minutes</strong> and can only be used once.
          </p>

          {{-- Code box --}}
          <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
            <tr>
              <td align="center" style="background:#f0f4ff;border:2px dashed #93c5fd;border-radius:12px;padding:28px 20px;">
                <p style="margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#64748b;">
                  Your Verification Code
                </p>
                <p style="margin:0;font-size:42px;font-weight:900;letter-spacing:14px;color:#1e3a8a;font-family:'Courier New',Courier,monospace;">
                  {{ $code }}
                </p>
                <p style="margin:10px 0 0;font-size:12px;color:#94a3b8;">
                  &#9201; Expires in <strong>5 minutes</strong>
                </p>
              </td>
            </tr>
          </table>

          {{-- Role info --}}
          @php $role = ucwords(str_replace('_', ' ', $user->getRoleNames()->first() ?? 'User')); @endphp
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:24px;">
            <tr>
              <td style="padding:12px 16px;font-size:13px;color:#475569;">
                <strong style="color:#1e293b;">Account:</strong> {{ $user->email }}<br>
                <strong style="color:#1e293b;">Role:</strong> {{ $role }}<br>
                <strong style="color:#1e293b;">Time:</strong> {{ now()->setTimezone('Asia/Manila')->format('M d, Y h:i A') }} (Manila)
              </td>
            </tr>
          </table>

          {{-- Warning --}}
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td style="background:#fef3c7;border-left:4px solid #f59e0b;border-radius:6px;padding:12px 16px;font-size:13px;color:#92400e;">
                <strong>&#9888; Security Notice:</strong> Never share this code with anyone.
                Parish staff will never ask for your verification code.
                If you did not attempt to sign in, please contact the administrator immediately.
              </td>
            </tr>
          </table>
        </td>
      </tr>

      {{-- Footer --}}
      <tr>
        <td style="background:#f8faff;border-top:1px solid #e2e8f0;padding:18px 40px;text-align:center;">
          <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
            &copy; {{ date('Y') }} {{ config('parish.name') }} &middot; Diocese of San Pablo<br>
            This is an automated message &mdash; please do not reply to this email.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>

</body>
</html>
