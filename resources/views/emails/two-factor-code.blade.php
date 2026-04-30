<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        body { margin: 0; padding: 0; background: #f0f4f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrap { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #1e3a8a, #2563eb); padding: 32px 40px; text-align: center; }
        .header img { width: 64px; height: 64px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.4); margin-bottom: 12px; }
        .header h1 { color: #fff; font-size: 1.25rem; font-weight: 700; margin: 0; }
        .header p  { color: #bfdbfe; font-size: 0.875rem; margin: 4px 0 0; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 1rem; color: #0f172a; font-weight: 600; margin-bottom: 12px; }
        .message  { font-size: 0.9rem; color: #475569; line-height: 1.7; margin-bottom: 28px; }
        .otp-box  { background: #f0f4f8; border: 2px dashed #bfdbfe; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 28px; }
        .otp-label { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #64748b; margin-bottom: 8px; }
        .otp-code  { font-size: 2.5rem; font-weight: 800; letter-spacing: 0.3em; color: #1e3a8a; font-family: 'Courier New', monospace; }
        .otp-expiry { font-size: 0.8rem; color: #94a3b8; margin-top: 8px; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 12px 16px; font-size: 0.8rem; color: #92400e; margin-bottom: 24px; }
        .footer { background: #f8faff; border-top: 1px solid #e2e8f0; padding: 20px 40px; text-align: center; }
        .footer p { font-size: 0.75rem; color: #94a3b8; margin: 0; line-height: 1.6; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo">
        <h1>{{ config('parish.name') }}</h1>
        <p>Southville 1, Niugan, Cabuyao, Laguna</p>
    </div>
    <div class="body">
        <p class="greeting">Hello, {{ $user->name }}!</p>
        <p class="message">
            You are attempting to sign in to the MHC Parish Parishioner Portal.
            Use the verification code below to complete your login.
        </p>
        <div class="otp-box">
            <p class="otp-label">Your Verification Code</p>
            <p class="otp-code">{{ $code }}</p>
            <p class="otp-expiry">⏱ This code expires in <strong>10 minutes</strong></p>
        </div>
        <div class="warning">
            <strong>Security Notice:</strong> Never share this code with anyone.
            Parish staff will never ask for your verification code.
            If you did not request this, please ignore this email.
        </div>
        <p class="message" style="margin-bottom:0;">
            God bless,<br>
            <strong>{{ config('parish.name') }}</strong>
        </p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} {{ config('parish.name') }} · Diocese of San Pablo</p>
        <p>This is an automated message. Please do not reply.</p>
    </div>
</div>
</body>
</html>
