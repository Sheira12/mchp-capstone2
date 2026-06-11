<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed — MHC Parish</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background: linear-gradient(135deg, #065f46 0%, #059669 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 1.5rem 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wrap { width: 100%; max-width: 500px; }
        .card {
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 12px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .card-head {
            background: linear-gradient(135deg, #065f46, #10b981);
            padding: 2rem 1.5rem;
            text-align: center;
            color: #fff;
        }
        .check-circle {
            width: 72px; height: 72px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            animation: pop 0.5s cubic-bezier(0.175,0.885,0.32,1.275);
        }
        @keyframes pop { 0%{transform:scale(0)} 80%{transform:scale(1.1)} 100%{transform:scale(1)} }
        .card-body { padding: 1.5rem; }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.625rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { color: #64748b; font-weight: 500; flex-shrink: 0; padding-right: 1rem; }
        .detail-value { color: #0f172a; font-weight: 700; text-align: right; }
        .qr-section {
            text-align: center;
            padding: 1.25rem;
            background: #f8faff;
            border-top: 1px solid #e8f0fe;
            border-bottom: 1px solid #e8f0fe;
            margin: 0.5rem 0;
        }
        .qr-section img { width: 140px; height: 140px; display: block; margin: 0 auto 0.5rem; }
        .ref-num {
            font-family: 'Courier New', monospace;
            font-size: 1.125rem;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 2px;
            background: #eff6ff;
            border: 2px dashed #bfdbfe;
            border-radius: 0.625rem;
            padding: 0.625rem 1rem;
            display: inline-block;
        }
        .btn-print {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #fff;
            font-weight: 700;
            font-size: 0.9375rem;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-print:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(37,99,235,0.35); }
        .btn-new {
            width: 100%;
            padding: 0.875rem;
            background: #fff;
            color: #374151;
            font-weight: 600;
            font-size: 0.9375rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.2s;
            margin-top: 0.625rem;
        }
        .btn-new:hover { border-color: #2563eb; color: #2563eb; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">

        {{-- Success header --}}
        <div class="card-head">
            <div class="check-circle">
                <svg style="width:36px;height:36px;" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 style="font-size:1.375rem;font-weight:800;margin:0 0 4px;">Booking Submitted!</h1>
            <p style="color:rgba(255,255,255,0.85);font-size:0.9rem;margin:0;">
                Your walk-in booking has been received.<br>
                The parish office will confirm your schedule.
            </p>
        </div>

        {{-- Booking details --}}
        <div class="card-body">

            <div style="margin-bottom:1rem;">
                <p style="font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;margin-bottom:0.5rem;">Booking Reference</p>
                <div style="text-align:center;">
                    <span class="ref-num">{{ $booking->reference_number }}</span>
                </div>
            </div>

            <div style="margin-bottom:1rem;">
                <p style="font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;margin-bottom:0.5rem;">Booking Details</p>
                <div>
                    <div class="detail-row">
                        <span class="detail-label">Parishioner</span>
                        <span class="detail-value">{{ $booking->parishioner->full_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact</span>
                        <span class="detail-value">{{ $booking->parishioner->contact_number }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Service</span>
                        <span class="detail-value">{{ $booking->getTypeLabel() }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Requested Date</span>
                        <span class="detail-value">{{ $booking->scheduled_date->format('F d, Y') }}</span>
                    </div>
                    @if($booking->scheduled_time)
                    <div class="detail-row">
                        <span class="detail-label">Preferred Time</span>
                        <span class="detail-value">{{ \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Service Fee</span>
                        <span class="detail-value">{{ $booking->service_fee > 0 ? '₱'.number_format($booking->service_fee,2) : 'Free / Donation' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">
                            <span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:9999px;font-size:0.75rem;font-weight:700;">
                                ⏳ Pending Approval
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- QR Code --}}
            @if($qrBase64)
            <div class="qr-section">
                <p style="font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;margin-bottom:0.75rem;">Your Booking QR Code</p>
                <img src="{{ $qrBase64 }}" alt="Booking QR Code">
                <p style="font-size:0.8rem;color:#64748b;margin:0;">Present this QR at the parish office for verification</p>
            </div>
            @endif

            {{-- Instructions --}}
            <div style="background:#eff6ff;border-radius:0.75rem;padding:1rem;margin-bottom:1.25rem;">
                <p style="font-weight:700;color:#1e3a8a;font-size:0.875rem;margin:0 0 6px;">What happens next?</p>
                <ol style="margin:0;padding-left:1.25rem;color:#1e40af;font-size:0.8125rem;line-height:1.7;">
                    <li>Print this page or the stub using the button below.</li>
                    <li>Keep the QR code — present it at the parish office.</li>
                    <li>Parish staff will contact you to confirm the schedule.</li>
                    <li>Bring required documents on your scheduled date.</li>
                </ol>
            </div>

            {{-- Actions --}}
            <a href="{{ route('walkin.print', $booking) }}" target="_blank" class="btn-print">
                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                🖨️ Print Booking Stub
            </a>

            <a href="{{ route('walkin.index') }}" class="btn-new">
                <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Submit Another Booking
            </a>

        </div>
    </div>

    <p style="text-align:center;color:rgba(255,255,255,0.6);font-size:0.75rem;margin-top:1rem;">
        Mary Help of Christians Parish · Tel: {{ config('parish.phone') }}
    </p>
</div>
</body>
</html>
