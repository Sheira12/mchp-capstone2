<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Stub — {{ $booking->reference_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            gap: 16px;
        }
        .no-print {
            display: flex;
            gap: 12px;
            margin-bottom: 8px;
        }
        .btn {
            padding: 10px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print-action { background: #1e3a8a; color: #fff; }
        .btn-back { background: #f3f4f6; color: #374151; border: 1.5px solid #d1d5db; }

        /* Stub card — 80mm receipt width for thermal printer, or A5 for inkjet */
        .stub {
            width: 320px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .stub-header {
            background: linear-gradient(135deg, #1F3A5F, #2d5282);
            padding: 16px 20px;
            text-align: center;
            color: #fff;
        }
        .stub-header img {
            width: 52px; height: 52px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.5);
            margin-bottom: 8px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .stub-header .parish-name {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .stub-header .parish-addr {
            font-size: 9px;
            color: rgba(255,255,255,0.7);
        }
        .stub-header .stub-type {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #D4AF37;
            margin-top: 8px;
            border-top: 1px solid rgba(255,255,255,0.15);
            padding-top: 8px;
        }
        .stub-body { padding: 14px 16px; }
        .stub-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 5px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }
        .stub-row:last-child { border-bottom: none; }
        .stub-label {
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-size: 9px;
            padding-top: 1px;
        }
        .stub-value { color: #111827; font-weight: 700; text-align: right; max-width: 60%; }
        .status-badge {
            background: #fef3c7;
            color: #92400e;
            font-size: 8px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .qr-section {
            padding: 14px 16px;
            text-align: center;
            border-top: 2px dashed #e5e7eb;
            background: #fafafa;
        }
        .qr-section img {
            width: 120px;
            height: 120px;
            margin: 0 auto 6px;
            display: block;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 4px;
            background: #fff;
        }
        .qr-url { font-size: 8px; color: #9ca3af; word-break: break-all; }
        .stub-footer {
            padding: 10px 16px;
            background: #1F3A5F;
            text-align: center;
        }
        .stub-footer .ref-big {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            font-weight: 800;
            color: #D4AF37;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }
        .stub-footer p {
            font-size: 8px;
            color: rgba(255,255,255,0.6);
            line-height: 1.5;
        }
        /* Print styles */
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .stub { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn btn-print-action">
        🖨️ Print Stub
    </button>
    <a href="{{ route('walkin.confirmation', $booking) }}" class="btn btn-back" style="text-decoration:none;">
        ← Back
    </a>
    <a href="{{ route('walkin.index') }}" class="btn btn-back" style="text-decoration:none;">
        + New Booking
    </a>
</div>

<div class="stub">

    {{-- Header --}}
    <div class="stub-header">
        <img src="{{ asset('images/parish-logo.png') }}" alt="MHC Parish"
             onerror="this.style.display='none'">
        <div class="parish-name">Mary Help of Christians Parish</div>
        <div class="parish-addr">Southville 1, Niugan, Cabuyao, Laguna</div>
        <div class="stub-type">Walk-in Booking Stub</div>
    </div>

    {{-- Details --}}
    <div class="stub-body">
        <div class="stub-row">
            <span class="stub-label">Reference</span>
            <span class="stub-value" style="font-family:monospace;font-size:10px;">{{ $booking->reference_number }}</span>
        </div>
        <div class="stub-row">
            <span class="stub-label">Name</span>
            <span class="stub-value">{{ $booking->parishioner->full_name }}</span>
        </div>
        <div class="stub-row">
            <span class="stub-label">Contact</span>
            <span class="stub-value">{{ $booking->parishioner->contact_number }}</span>
        </div>
        <div class="stub-row">
            <span class="stub-label">Service</span>
            <span class="stub-value">{{ $booking->getTypeLabel() }}</span>
        </div>
        <div class="stub-row">
            <span class="stub-label">Date</span>
            <span class="stub-value">{{ $booking->scheduled_date->format('M d, Y') }}</span>
        </div>
        <div class="stub-row">
            <span class="stub-label">Time</span>
            <span class="stub-value">
                {{ $booking->scheduled_time
                    ? \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A')
                    : 'To be confirmed' }}
            </span>
        </div>
        <div class="stub-row">
            <span class="stub-label">Fee</span>
            <span class="stub-value">
                {{ $booking->service_fee > 0 ? '₱'.number_format($booking->service_fee,2) : 'Free / Donation' }}
            </span>
        </div>
        <div class="stub-row">
            <span class="stub-label">Status</span>
            <span class="stub-value">
                <span class="status-badge">⏳ Pending</span>
            </span>
        </div>
        <div class="stub-row">
            <span class="stub-label">Submitted</span>
            <span class="stub-value">{{ now()->format('M d, Y g:i A') }}</span>
        </div>
    </div>

    {{-- QR Code --}}
    <div class="qr-section">
        <div style="font-size:8px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#9ca3af;margin-bottom:6px;">Scan to verify booking</div>
        @if($qrBase64)
            <img src="{{ $qrBase64 }}" alt="QR Code">
        @else
            <div style="width:120px;height:120px;border:2px dashed #d1d5db;border-radius:8px;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;color:#9ca3af;font-size:9px;">
                QR Code<br>Generating...
            </div>
        @endif
        <div class="qr-url">{{ $booking->qrCode?->verification_url ?? config('app.url').'/verify/...' }}</div>
    </div>

    {{-- Footer --}}
    <div class="stub-footer">
        <div class="ref-big">{{ $booking->reference_number }}</div>
        <p>Present this stub at the parish office on your scheduled date.<br>
        This booking is subject to approval. Valid for one use.</p>
    </div>

</div>

<p style="font-size:11px;color:#9ca3af;text-align:center;max-width:320px;">
    For inquiries: {{ config('parish.phone') }} · Office hours: Mon–Fri 8AM–5PM, Sat 8AM–12PM
</p>

</body>
</html>
