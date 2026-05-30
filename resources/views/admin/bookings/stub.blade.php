<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Walk-in Stub — {{ $booking->reference_number }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
}
.stub-wrap {
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: center;
}
.stub {
    width: 320px;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
    border: 1px solid #e5e7eb;
}
.stub-header {
    background: linear-gradient(135deg, #1F3A5F, #2d5282);
    padding: 16px 20px;
    text-align: center;
    color: #fff;
}
.stub-header .parish-name {
    font-size: 13px;
    font-weight: bold;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}
.stub-header .parish-addr {
    font-size: 9px;
    color: rgba(255,255,255,0.7);
}
.stub-header .stub-title {
    font-size: 10px;
    font-weight: bold;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #D4AF37;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid rgba(255,255,255,0.15);
}
.stub-body {
    padding: 16px 20px;
}
.stub-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 6px 0;
    border-bottom: 1px solid #f3f4f6;
    font-size: 11px;
}
.stub-row:last-child { border-bottom: none; }
.stub-row .label {
    color: #9ca3af;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 9px;
    padding-top: 1px;
}
.stub-row .value {
    color: #111827;
    font-weight: bold;
    text-align: right;
    max-width: 60%;
}
.stub-row .value.ref {
    font-family: 'Courier New', monospace;
    font-size: 10px;
    color: #1F3A5F;
}
.status-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-pending   { background: #fef3c7; color: #92400e; }
.status-confirmed { background: #d1fae5; color: #065f46; }
.status-completed { background: #dbeafe; color: #1e40af; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.qr-section {
    padding: 16px 20px;
    text-align: center;
    border-top: 2px dashed #e5e7eb;
    background: #fafafa;
}
.qr-section img {
    width: 120px;
    height: 120px;
    margin: 0 auto 8px;
    display: block;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 4px;
    background: #fff;
}
.qr-section .qr-label {
    font-size: 9px;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 4px;
}
.qr-section .qr-url {
    font-size: 8px;
    color: #6b7280;
    word-break: break-all;
}
.stub-footer {
    padding: 10px 20px;
    background: #1F3A5F;
    text-align: center;
}
.stub-footer p {
    font-size: 8px;
    color: rgba(255,255,255,0.6);
    line-height: 1.5;
}
.stub-footer .ref-big {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    color: #D4AF37;
    font-weight: bold;
    letter-spacing: 1px;
    margin-bottom: 3px;
}

/* Print styles */
@media print {
    body { background: #fff; padding: 0; }
    .no-print { display: none !important; }
    .stub { box-shadow: none; border: 1px solid #ccc; }
    .stub-wrap { gap: 0; }
}
</style>
</head>
<body>

<div class="stub-wrap">

    {{-- Print / Back buttons --}}
    <div class="no-print flex gap-3">
        <button onclick="window.print()"
                style="background:#1F3A5F;color:#fff;border:none;padding:10px 24px;border-radius:10px;font-weight:bold;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:8px;">
            🖨️ Print Stub
        </button>
        <a href="{{ route('admin.bookings.show', $booking) }}"
           style="background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:10px 24px;border-radius:10px;font-weight:bold;font-size:13px;text-decoration:none;display:flex;align-items:center;gap:8px;">
            ← Back to Booking
        </a>
    </div>

    {{-- Stub card --}}
    <div class="stub">

        {{-- Header --}}
        <div class="stub-header">
            <div class="parish-name">Mary Help of Christians Parish</div>
            <div class="parish-addr">Southville 1, Niugan, Cabuyao, Laguna</div>
            <div class="stub-title">Walk-in Booking Stub</div>
        </div>

        {{-- Details --}}
        <div class="stub-body">
            <div class="stub-row">
                <span class="label">Reference</span>
                <span class="value ref">{{ $booking->reference_number }}</span>
            </div>
            <div class="stub-row">
                <span class="label">Parishioner</span>
                <span class="value">{{ $booking->parishioner->full_name }}</span>
            </div>
            <div class="stub-row">
                <span class="label">Service</span>
                <span class="value">{{ $booking->getTypeLabel() }}</span>
            </div>
            <div class="stub-row">
                <span class="label">Date</span>
                <span class="value">{{ $booking->scheduled_date->format('M d, Y') }}</span>
            </div>
            <div class="stub-row">
                <span class="label">Time</span>
                <span class="value">
                    {{ $booking->scheduled_time
                        ? \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A')
                        : 'To be confirmed' }}
                </span>
            </div>
            <div class="stub-row">
                <span class="label">Fee</span>
                <span class="value">
                    {{ $booking->service_fee > 0 ? '₱'.number_format($booking->service_fee,2) : 'Free / Donation' }}
                </span>
            </div>
            <div class="stub-row">
                <span class="label">Status</span>
                <span class="value">
                    <span class="status-badge status-{{ $booking->status }}">{{ $booking->getStatusLabel() }}</span>
                </span>
            </div>
            <div class="stub-row">
                <span class="label">Issued</span>
                <span class="value">{{ now()->format('M d, Y g:i A') }}</span>
            </div>
        </div>

        {{-- QR Code --}}
        <div class="qr-section">
            <div class="qr-label">Scan QR to verify</div>
            @if($qrBase64)
                <img src="{{ $qrBase64 }}" alt="QR Code">
            @else
                <div style="width:120px;height:120px;border:2px dashed #d1d5db;border-radius:8px;display:flex;align-items:center;justify-content:center;margin:0 auto 8px;color:#9ca3af;font-size:10px;">No QR</div>
            @endif
            <div class="qr-url">{{ $booking->qrCode?->verification_url ?? config('app.url').'/verify/...' }}</div>
        </div>

        {{-- Footer --}}
        <div class="stub-footer">
            <div class="ref-big">{{ $booking->reference_number }}</div>
            <p>Present this stub at the parish office on your scheduled date.<br>
            Keep this for your records. Valid for one-time use.</p>
        </div>

    </div>

    {{-- Instructions --}}
    <div class="no-print" style="width:320px;background:#fff;border-radius:12px;padding:16px;border:1px solid #e5e7eb;font-size:12px;color:#6b7280;line-height:1.6;">
        <strong style="color:#374151;display:block;margin-bottom:6px;">📋 Instructions for Staff:</strong>
        1. Print this stub and give it to the parishioner.<br>
        2. On the day of service, scan the QR code using the <a href="{{ route('admin.bookings.qr-scanner') }}" style="color:#2563eb;">QR Scanner</a>.<br>
        3. Verify the booking details match the parishioner's ID.<br>
        4. Mark the booking as Completed after service is rendered.
    </div>

</div>

</body>
</html>
