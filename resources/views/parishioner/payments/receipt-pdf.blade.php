<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/* ═══════════════════════════════════════════════════════════════
   OFFICIAL PAYMENT RECEIPT — A4 Portrait
   Mary Help of Christians Parish
   Engine: DomPDF v2  |  @page margin: 6mm
   Navy #1F3A5F · Gold #D4AF37 · Green #065F46
   ═══════════════════════════════════════════════════════════════ */

@page {
    size: A4 portrait;
    margin: 6mm;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    margin: 0; padding: 0;
    background: #fff;
    font-family: 'Times New Roman', Georgia, serif;
    color: #1a1a2e;
    font-size: 9pt;
    line-height: 1.3;
}

/* ── Border frame (fixed = repeats on all pages, but we have 1) ── */
.border-frame {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    border: 2.5pt solid #D4AF37;
    z-index: 0;
    pointer-events: none;
}
.border-frame-inner {
    position: fixed;
    top: 3pt; left: 3pt; right: 3pt; bottom: 3pt;
    border: 0.75pt solid #1F3A5F;
    z-index: 0;
    pointer-events: none;
}

/* ── Watermark ── */
.watermark {
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 48pt;
    color: rgba(31,58,95,0.03);
    font-weight: 900;
    white-space: nowrap;
    pointer-events: none;
    z-index: 0;
    font-family: Arial, sans-serif;
    letter-spacing: 4pt;
}

/* ── Content ── */
.content { position: relative; z-index: 1; }

/* ── Header ── */
.header {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 1.5pt solid #D4AF37;
    padding-bottom: 6pt;
    margin-bottom: 5pt;
    display: table;
}
.header-left  { display: table-cell; vertical-align: middle; width: 18%; }
.header-center { display: table-cell; vertical-align: middle; text-align: center; width: 64%; }
.header-right { display: table-cell; vertical-align: middle; text-align: right; width: 18%; }

.logo-circle {
    width: 44pt; height: 44pt; border-radius: 50%;
    border: 1.5pt solid #D4AF37;
    overflow: hidden;
    display: block;
    background: #fff;
    text-align: center;
    line-height: 44pt;
}
.logo-circle img { width: 44pt; height: 44pt; display: block; }

.diocese-label {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; color: #D4AF37;
    letter-spacing: 2pt; text-transform: uppercase;
    margin-bottom: 1.5pt;
}
.parish-name {
    font-size: 12pt; font-weight: bold; color: #1F3A5F;
    letter-spacing: 0.5pt; margin-bottom: 1pt;
}
.parish-address {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; color: #6b7280; line-height: 1.5;
}

.or-box {
    border: 1.5pt solid #1F3A5F;
    border-radius: 5pt;
    padding: 3pt 5pt;
    text-align: center;
}
.or-label {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 5.5pt; font-weight: bold;
    letter-spacing: 1.5pt; text-transform: uppercase;
    color: #6b7280; margin-bottom: 1pt;
}
.or-number {
    font-family: 'Courier New', monospace;
    font-size: 8pt; font-weight: bold; color: #1F3A5F;
}
.or-date {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 5.5pt; color: #6b7280; margin-top: 1pt;
}

/* ── Document title ── */
.doc-title-wrap { text-align: center; margin-bottom: 4pt; }
.doc-title {
    font-size: 16pt; font-weight: bold; color: #1F3A5F;
    letter-spacing: 3pt; text-transform: uppercase;
}
.doc-subtitle {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; color: #D4AF37;
    letter-spacing: 1.5pt; text-transform: uppercase;
    margin-top: 1pt;
}

/* ── Gold divider ── */
.gold-line { border: none; border-top: 0.75pt solid #D4AF37; margin: 3pt 0; }

/* ── Payer info ── */
.payer-section {
    background: #f8faff;
    border: 0.75pt solid #e8f0fe;
    border-radius: 5pt;
    padding: 4pt 5pt;
    margin-bottom: 4pt;
    display: table;
    width: 100%;
}
.payer-left  { display: table-cell; width: 60%; vertical-align: top; }
.payer-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
.payer-label {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 5.5pt; font-weight: bold;
    letter-spacing: 1pt; text-transform: uppercase;
    color: #D4AF37; margin-bottom: 1pt;
}
.payer-value { font-size: 10pt; font-weight: bold; color: #1F3A5F; }
.payer-sub {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; color: #6b7280; margin-top: 0.5pt;
}

/* ── Items table ── */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 3pt; }
.items-table thead tr { background: #1F3A5F; }
.items-table thead th {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; font-weight: bold;
    letter-spacing: 0.75pt; text-transform: uppercase;
    color: #fff; padding: 3pt 4pt; text-align: left;
}
.items-table thead th:last-child { text-align: right; }
.items-table tbody tr { border-bottom: 0.5pt solid #e5e7eb; }
.items-table tbody tr:last-child { border-bottom: none; }
.items-table tbody td { padding: 3pt 4pt; font-size: 9pt; color: #1a1a2e; vertical-align: top; }
.items-table tbody td:last-child { text-align: right; font-weight: bold; }
.item-desc { font-weight: bold; color: #1F3A5F; }
.item-sub { font-family: Arial, Helvetica, sans-serif; font-size: 6.5pt; color: #6b7280; margin-top: 1pt; }

/* ── Totals ── */
.totals-table { width: 100%; border-collapse: collapse; margin-bottom: 4pt; }
.totals-table td { padding: 1.5pt 4pt; font-family: Arial, Helvetica, sans-serif; font-size: 8pt; }
.totals-table .total-row td {
    background: #1F3A5F; color: #fff; font-weight: bold;
    font-size: 9.5pt; padding: 3pt 4pt;
}
.totals-table .total-row td:last-child { text-align: right; font-size: 11pt; }
.totals-table .sub-row td { color: #6b7280; }
.totals-table .sub-row td:last-child { text-align: right; }

/* ── Payment method badge ── */
.method-badge {
    display: inline-block; padding: 1.5pt 4pt; border-radius: 10pt;
    font-family: Arial, Helvetica, sans-serif; font-size: 7pt; font-weight: bold;
}
.method-gcash { background: #EFF6FF; color: #007DFE; border: 0.75pt solid #BFDBFE; }
.method-maya  { background: #F0FDF4; color: #00B140; border: 0.75pt solid #BBF7D0; }
.method-cash  { background: #FFFBEB; color: #92400E; border: 0.75pt solid #FDE68A; }

/* ── Status badge ── */
.status-paid {
    display: inline-block; background: #D1FAE5; color: #065F46;
    border: 1pt solid #6EE7B7; border-radius: 10pt;
    padding: 1.5pt 5pt; font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; font-weight: bold; letter-spacing: 0.75pt; text-transform: uppercase;
}

/* ── Amount in words ── */
.amount-words {
    background: #f8faff; border: 0.75pt solid #e8f0fe;
    border-radius: 5pt; padding: 2.5pt 4pt; margin-bottom: 4pt;
}

/* ── Verification strip ── */
.verify-strip {
    background: #1F3A5F; border-radius: 4pt;
    padding: 2pt 4pt; margin-bottom: 3pt;
    display: table; width: 100%;
}
.verify-strip-left  { display: table-cell; vertical-align: middle; width: 70%; }
.verify-strip-right { display: table-cell; vertical-align: middle; text-align: right; width: 30%; }
.verify-text { font-family: Arial, Helvetica, sans-serif; font-size: 6pt; color: rgba(255,255,255,0.8); }
.verify-url  { font-family: 'Courier New', monospace; font-size: 5.5pt; color: #93c5fd; }

/* ── Bottom section ── */
.bottom-section {
    display: table; width: 100%;
    border-top: 0.75pt solid #e5e7eb; padding-top: 3pt;
}
.bottom-left   { display: table-cell; width: 25%; vertical-align: bottom; }
.bottom-center { display: table-cell; width: 50%; vertical-align: bottom; text-align: center; }
.bottom-right  { display: table-cell; width: 25%; vertical-align: bottom; text-align: right; }

.qr-wrap { text-align: center; }
.qr-wrap img { width: 38pt; height: 38pt; display: block; margin: 0 auto; }
.qr-label { font-family: Arial, Helvetica, sans-serif; font-size: 5pt; color: #9ca3af; margin-top: 1pt; }

.sig-line { border-top: 0.75pt solid #1F3A5F; padding-top: 1.5pt; margin-top: 14pt; }
.sig-name { font-size: 8pt; font-weight: bold; color: #1F3A5F; }
.sig-title {
    font-family: Arial, Helvetica, sans-serif; font-size: 5.5pt; color: #6b7280;
    letter-spacing: 0.5pt; text-transform: uppercase; margin-top: 0.5pt;
}

/* ── Footer ── */
.doc-footer {
    position: fixed; bottom: 0; left: 0; right: 0;
    border-top: 0.75pt solid rgba(212,175,55,0.5);
    padding-top: 2pt; display: table; width: 100%;
    z-index: 1;
}
.footer-left  { display: table-cell; vertical-align: middle; width: 50%; }
.footer-right { display: table-cell; vertical-align: middle; text-align: right; width: 50%; }
.footer-text  { font-family: Arial, Helvetica, sans-serif; font-size: 5.5pt; color: #9ca3af; line-height: 1.5; }
.footer-certno { font-family: 'Courier New', monospace; font-size: 6pt; color: #6b7280; font-weight: bold; }
</style>
</head>
<body>
@php
$methodLabels = ['gcash'=>'GCash','maya'=>'Maya','cash'=>'Cash','bank'=>'Bank Transfer'];
$methodLabel  = $methodLabels[$payment->payment_method] ?? ucfirst($payment->payment_method);
$methodClass  = 'method-' . ($payment->payment_method === 'paymaya' ? 'maya' : $payment->payment_method);
$paidDate     = $payment->paid_at ?? $payment->created_at;

$receiptQrData = config('app.url') . '/portal/payments/receipt/' . $payment->id;
$qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(80)->margin(0)->errorCorrection('H')->generate($receiptQrData);
$qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

if (!function_exists('amountInWords')) {
    function amountInWords(float $amount): string {
        $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                 'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                 'Seventeen','Eighteen','Nineteen'];
        $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        $n = (int) $amount;
        $cents = round(($amount - $n) * 100);
        if ($n === 0) return 'Zero';
        $words = '';
        if ($n >= 1000) { $words .= $ones[(int)($n/1000)] . ' Thousand '; $n %= 1000; }
        if ($n >= 100)  { $words .= $ones[(int)($n/100)] . ' Hundred '; $n %= 100; }
        if ($n >= 20)   { $words .= $tens[(int)($n/10)] . ' '; $n %= 10; }
        if ($n > 0)     { $words .= $ones[$n] . ' '; }
        return trim($words) . ' Pesos' . ($cents > 0 ? ' and ' . $cents . '/100' : ' Only');
    }
}
@endphp

{{-- Border & watermark --}}
<div class="border-frame"></div>
<div class="border-frame-inner"></div>
<div class="watermark">OFFICIAL RECEIPT</div>

{{-- Fixed footer --}}
<div class="doc-footer">
    <div class="footer-left">
        <div class="footer-text">{{ $parish['name'] }} &nbsp;·&nbsp; {{ $parish['address'] }} &nbsp;·&nbsp; Tel: {{ $parish['phone'] }}</div>
    </div>
    <div class="footer-right">
        <div class="footer-certno">{{ $payment->receipt_number }}</div>
        <div class="footer-text">Issued: {{ $paidDate->format('F d, Y g:i A') }}</div>
    </div>
</div>

<div class="content">

    {{-- ── HEADER ── --}}
    <div class="header">
        <div class="header-left">
            <div class="logo-circle">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Parish Logo">
                @else
                    <svg width="44pt" height="44pt" viewBox="0 0 44 44" fill="none">
                        <circle cx="22" cy="22" r="20" stroke="#D4AF37" stroke-width="1.5"/>
                        <text x="22" y="27" text-anchor="middle" font-family="Georgia,serif" font-size="9" font-weight="bold" fill="#1F3A5F">MHC</text>
                    </svg>
                @endif
            </div>
        </div>
        <div class="header-center">
            <div class="diocese-label">Diocese of San Pablo &nbsp;·&nbsp; Archdiocese of Lipa</div>
            <div class="parish-name">{{ $parish['name'] }}</div>
            <div class="parish-address">
                {{ $parish['address'] }}<br>
                Tel: {{ $parish['phone'] }} &nbsp;·&nbsp; {{ $parish['email'] }}
            </div>
        </div>
        <div class="header-right">
            <div class="or-box">
                <div class="or-label">Official Receipt</div>
                <div class="or-number">{{ $payment->receipt_number }}</div>
                <div class="or-date">{{ $paidDate->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    {{-- ── TITLE ── --}}
    <div class="doc-title-wrap">
        <div class="doc-title">Official Receipt</div>
        <div class="doc-subtitle">Resibo ng Bayad &nbsp;·&nbsp; Acknowledgement of Payment</div>
    </div>

    <hr class="gold-line">

    {{-- ── PAYER INFO ── --}}
    <div class="payer-section">
        <div class="payer-left">
            <div class="payer-label">Received From</div>
            <div class="payer-value">{{ $payment->parishioner->full_name }}</div>
            <div class="payer-sub">
                {{ $payment->parishioner->address ?? '' }}
                @if($payment->parishioner->barangay), Brgy. {{ $payment->parishioner->barangay }}@endif
                @if($payment->parishioner->city), {{ $payment->parishioner->city }}@endif
            </div>
            @if($payment->payer_contact ?? $payment->parishioner->contact_number)
            <div class="payer-sub">Tel: {{ $payment->payer_contact ?? $payment->parishioner->contact_number }}</div>
            @endif
        </div>
        <div class="payer-right">
            <div class="payer-label">Payment Status</div>
            <div style="margin-bottom:2pt;"><span class="status-paid">PAID</span></div>
            <div class="payer-label" style="margin-top:4pt;">Transaction Type</div>
            <div style="margin-bottom:2pt;">
                @php
                    $txType  = $payment->transaction_type ?? 'debit';
                    $txColor = $txType === 'credit' ? '#065f46' : '#991b1b';
                    $txBg    = $txType === 'credit' ? '#d1fae5'  : '#fee2e2';
                    $txArrow = '';
                @endphp
                <span style="display:inline-block;padding:1.5pt 5pt;border-radius:10pt;font-family:Arial,sans-serif;font-size:7pt;font-weight:bold;background:{{ $txBg }};color:{{ $txColor }};">
                    {{ $txArrow }} {{ strtoupper($txType) }}
                </span>
            </div>
            <div class="payer-label" style="margin-top:4pt;">Payment Method</div>
            <span class="{{ $methodClass }} method-badge">{{ $methodLabel }}</span>
            @if($payment->submitted_reference)
            <div class="payer-sub" style="margin-top:1pt;font-family:monospace;font-size:6.5pt;">Ref: {{ $payment->submitted_reference }}</div>
            @endif
        </div>
    </div>

    {{-- ── ITEMS TABLE ── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:55%;">Description</th>
                <th style="width:20%;">Date</th>
                <th style="width:20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="color:#6b7280;font-size:7.5pt;">1</td>
                <td>
                    <div class="item-desc">
                        @if($payment->booking){{ $payment->booking->getTypeLabel() }}
                        @else Parish Service Payment @endif
                    </div>
                    @if($payment->booking)
                    <div class="item-sub">
                        Booking Ref: {{ $payment->booking->reference_number }}
                        @if($payment->booking->scheduled_date) &nbsp;·&nbsp; Scheduled: {{ $payment->booking->scheduled_date->format('F d, Y') }}@endif
                    </div>
                    @endif
                    @if($payment->notes)<div class="item-sub">{{ $payment->notes }}</div>@endif
                </td>
                <td style="font-family:Arial,sans-serif;font-size:7.5pt;color:#6b7280;">{{ $paidDate->format('M d, Y') }}</td>
                <td>&#8369;{{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── TOTALS ── --}}
    <table class="totals-table">
        <tr class="sub-row">
            <td style="width:60%;"></td>
            <td style="width:25%;color:#6b7280;">Subtotal</td>
            <td style="width:15%;text-align:right;">&#8369;{{ number_format($payment->amount, 2) }}</td>
        </tr>
        <tr class="sub-row">
            <td></td>
            <td style="color:#6b7280;">Tax / Fees</td>
            <td style="text-align:right;">&#8369;0.00</td>
        </tr>
        <tr class="total-row">
            <td></td>
            <td>TOTAL AMOUNT PAID</td>
            <td>&#8369;{{ number_format($payment->amount, 2) }}</td>
        </tr>
    </table>

    {{-- ── AMOUNT IN WORDS ── --}}
    <div class="amount-words">
        <span style="font-family:Arial,sans-serif;font-size:6pt;font-weight:bold;color:#D4AF37;text-transform:uppercase;letter-spacing:0.75pt;">Amount in Words: </span>
        <span style="font-size:8.5pt;font-style:italic;color:#1F3A5F;font-weight:bold;">{{ amountInWords((float)$payment->amount) }}</span>
    </div>

    {{-- ── VERIFICATION STRIP ── --}}
    <div class="verify-strip">
        <div class="verify-strip-left">
            <div class="verify-text">Verify this receipt online at:</div>
            <div class="verify-url">{{ config('app.url') }}/portal/payments/receipt/{{ $payment->id }}</div>
        </div>
        <div class="verify-strip-right">
            <div class="verify-text" style="color:rgba(255,255,255,0.6);">Receipt No.</div>
            <div style="font-family:'Courier New',monospace;font-size:7.5pt;color:#fff;font-weight:bold;">{{ $payment->receipt_number }}</div>
        </div>
    </div>

    {{-- ── SIGNATURES + QR ── --}}
    <div class="bottom-section">
        <div class="bottom-left">
            <div class="qr-wrap">
                <img src="{{ $qrBase64 }}" alt="QR Code">
                <div class="qr-label">Scan to verify</div>
            </div>
        </div>
        <div class="bottom-center">
            <div style="font-family:Arial,sans-serif;font-size:6.5pt;color:#6b7280;text-align:center;margin-bottom:1.5pt;">
                This is an official receipt of payment issued by<br>
                <strong style="color:#1F3A5F;">{{ $parish['name'] }}</strong>
            </div>
            <div class="sig-line" style="width:65%;margin:0 auto;">
                <div class="sig-name" style="text-align:center;">{{ $parish['priest'] }}</div>
                <div class="sig-title" style="text-align:center;">Parish Priest</div>
            </div>
        </div>
        <div class="bottom-right">
            <div class="sig-line">
                <div class="sig-name">Parish Treasurer</div>
                <div class="sig-title">Finance Officer</div>
            </div>
        </div>
    </div>

</div>{{-- /content --}}
</body>
</html>
