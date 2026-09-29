<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/* ═══════════════════════════════════════════════════════════════
   OFFICIAL PAYMENT RECEIPT — A4 Portrait
   Mary Help of Christians Parish
   Engine: DomPDF v2
   Navy #1F3A5F · Gold #D4AF37 · Green #065F46
   ═══════════════════════════════════════════════════════════════ */

@page {
    size: A4 portrait;
    margin: 10mm 10mm 14mm 10mm;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    margin: 0; padding: 0;
    background: #fff;
    font-family: 'Times New Roman', Georgia, serif;
    color: #1a1a2e;
    font-size: 9.5pt;
    line-height: 1.35;
}

/* ── Outer gold border — wraps content, not fixed to page ── */
.border-outer {
    border: 2.5pt solid #D4AF37;
    padding: 3pt;
}
/* ── Inner navy border ── */
.border-inner {
    border: 0.75pt solid #1F3A5F;
    padding: 6pt;
}

/* ── Watermark: fixed so it stays centred on the rendered content area ── */
.watermark {
    position: fixed;
    top: 45%; left: 50%;
    transform: translate(-50%, -50%) rotate(-35deg);
    font-size: 52pt;
    color: rgba(31,58,95,0.05);
    font-weight: 900;
    white-space: nowrap;
    pointer-events: none;
    z-index: 0;
    font-family: Arial, sans-serif;
    letter-spacing: 6pt;
    text-transform: uppercase;
}

/* ── All real content sits above watermark ── */
.page { position: relative; z-index: 1; }

/* ════════════════════════════════
   HEADER
   ════════════════════════════════ */
.hdr {
    display: table;
    width: 100%;
    border-collapse: collapse;
    padding-bottom: 7pt;
    margin-bottom: 6pt;
    border-bottom: 2pt solid #D4AF37;
}
.hdr-logo   { display: table-cell; width: 13%; vertical-align: middle; }
.hdr-center { display: table-cell; width: 67%; vertical-align: middle; text-align: center; padding: 0 6pt; }
.hdr-box    { display: table-cell; width: 20%; vertical-align: middle; text-align: right; }

.logo-ring {
    width: 50pt; height: 50pt; border-radius: 50%;
    border: 2pt solid #D4AF37;
    overflow: hidden; display: block;
    background: #fff; text-align: center; line-height: 50pt;
}
.logo-ring img { width: 50pt; height: 50pt; display: block; }

.diocese-bar {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; color: #D4AF37;
    letter-spacing: 2.5pt; text-transform: uppercase;
    margin-bottom: 2pt;
}
.parish-name {
    font-size: 14pt; font-weight: bold; color: #1F3A5F;
    letter-spacing: 0.75pt; line-height: 1.2; margin-bottom: 2pt;
}
.parish-info {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; color: #6b7280; line-height: 1.6;
}

/* OR box — top-right */
.or-box {
    display: inline-block;
    border: 1.5pt solid #1F3A5F; border-radius: 5pt;
    padding: 4pt 6pt; min-width: 80pt; text-align: center;
}
.or-tag {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 5.5pt; font-weight: bold;
    letter-spacing: 2pt; text-transform: uppercase;
    color: #6b7280; margin-bottom: 2pt;
}
.or-num {
    font-family: 'Courier New', monospace;
    font-size: 8.5pt; font-weight: bold; color: #1F3A5F;
    letter-spacing: 0.5pt;
}
.or-dt {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; color: #9ca3af; margin-top: 2pt;
}

/* ════════════════════════════════
   DOCUMENT TITLE
   ════════════════════════════════ */
.title-wrap { text-align: center; margin: 5pt 0 4pt; }
.title-main {
    font-size: 18pt; font-weight: bold; color: #1F3A5F;
    letter-spacing: 4pt; text-transform: uppercase;
    line-height: 1;
}
.title-sub {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; color: #D4AF37;
    letter-spacing: 2pt; text-transform: uppercase;
    margin-top: 2pt;
}
.gold-rule { border: none; border-top: 1pt solid #D4AF37; margin: 4pt 0; }

/* ════════════════════════════════
   PAYER / PAYMENT INFO BAND
   ════════════════════════════════ */
.info-band {
    background: #f8faff;
    border: 1pt solid #e0e7ff;
    border-radius: 5pt;
    padding: 6pt 8pt;
    margin-bottom: 5pt;
    display: table;
    width: 100%;
}
.info-left  { display: table-cell; width: 55%; vertical-align: top; }
.info-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

.band-label {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; font-weight: bold;
    letter-spacing: 1.5pt; text-transform: uppercase;
    color: #D4AF37; display: block; margin-bottom: 1.5pt;
}
.payer-name { font-size: 11pt; font-weight: bold; color: #1F3A5F; }
.payer-detail {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; color: #6b7280; margin-top: 1.5pt; line-height: 1.5;
}

/* small status/method chips on the right */
.chip {
    display: inline-block; border-radius: 20pt;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7.5pt; font-weight: bold;
    padding: 2pt 7pt; letter-spacing: 0.5pt;
}
.chip-paid    { background: #d1fae5; color: #065f46; border: 1pt solid #6ee7b7; }
.chip-debit   { background: #fee2e2; color: #991b1b; }
.chip-credit  { background: #d1fae5; color: #065f46; }
.chip-gcash   { background: #eff6ff; color: #007DFE; border: 0.75pt solid #bfdbfe; }
.chip-maya    { background: #f0fdf4; color: #00B140; border: 0.75pt solid #bbf7d0; }
.chip-cash    { background: #fffbeb; color: #92400e; border: 0.75pt solid #fde68a; }
.chip-bank    { background: #f3f4f6; color: #374151; border: 0.75pt solid #d1d5db; }
.chip-card    { background: #eef2ff; color: #4338ca; border: 0.75pt solid #c7d2fe; }
.chip-spacing { margin-top: 3pt; }

.ref-mono {
    font-family: 'Courier New', monospace;
    font-size: 6.5pt; color: #6b7280; margin-top: 2pt;
}

/* ════════════════════════════════
   ITEMS TABLE
   ════════════════════════════════ */
.items { width: 100%; border-collapse: collapse; margin-bottom: 0; }
.items thead tr { background: #1F3A5F; }
.items thead th {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; font-weight: bold;
    letter-spacing: 1pt; text-transform: uppercase;
    color: #fff; padding: 4pt 5pt; text-align: left;
}
.items thead th.r { text-align: right; }
.items tbody tr { border-bottom: 0.5pt solid #f0f0f0; }
.items tbody tr:last-child { border-bottom: none; }
.items tbody td { padding: 5pt 5pt; font-size: 9.5pt; vertical-align: top; }
.items tbody td.r { text-align: right; font-weight: bold; color: #1F3A5F; }
.item-name { font-weight: bold; color: #1F3A5F; font-size: 10pt; }
.item-meta {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; color: #9ca3af; margin-top: 2pt; line-height: 1.5;
}

/* ════════════════════════════════
   TOTALS
   ════════════════════════════════ */
.totals { width: 100%; border-collapse: collapse; margin-top: 0; }
.totals td {
    padding: 2pt 5pt;
    font-family: Arial, Helvetica, sans-serif; font-size: 8pt;
}
.totals .sub td:last-child { text-align: right; }
.totals .sub td { color: #6b7280; }
.totals .grand { background: #1F3A5F; }
.totals .grand td { color: #fff; font-weight: bold; font-size: 10.5pt; padding: 4pt 5pt; }
.totals .grand td:last-child { text-align: right; font-size: 13pt; letter-spacing: -0.25pt; }

/* ════════════════════════════════
   AMOUNT IN WORDS
   ════════════════════════════════ */
.words-box {
    background: #fffdf0;
    border: 0.75pt solid #fde68a;
    border-radius: 4pt;
    padding: 3.5pt 6pt;
    margin: 4pt 0;
}
.words-label {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; font-weight: bold;
    color: #D4AF37; text-transform: uppercase; letter-spacing: 1pt;
}
.words-text {
    font-size: 9pt; font-style: italic; color: #1F3A5F; font-weight: bold;
    margin-top: 0.5pt;
}

/* ════════════════════════════════
   VERIFY STRIP
   ════════════════════════════════ */
.verify {
    background: #1F3A5F; border-radius: 4pt;
    padding: 3pt 6pt; margin: 4pt 0;
    display: table; width: 100%;
}
.verify-l { display: table-cell; vertical-align: middle; width: 72%; }
.verify-r { display: table-cell; vertical-align: middle; text-align: right; width: 28%; }
.v-label  { font-family: Arial, Helvetica, sans-serif; font-size: 6pt; color: rgba(255,255,255,0.7); }
.v-url    { font-family: 'Courier New', monospace; font-size: 5.5pt; color: #93c5fd; margin-top: 1pt; }
.v-rno    { font-family: 'Courier New', monospace; font-size: 8pt; color: #fff; font-weight: bold; }
.v-rno-l  { font-family: Arial, Helvetica, sans-serif; font-size: 5.5pt; color: rgba(255,255,255,0.6); display: block; margin-bottom: 1pt; }

/* ════════════════════════════════
   SIGNATURES + QR ROW
   ════════════════════════════════ */
.sig-row {
    display: table; width: 100%;
    border-top: 1pt solid #e5e7eb;
    padding-top: 6pt; margin-top: 4pt;
}
.sig-qr     { display: table-cell; width: 20%; vertical-align: bottom; text-align: center; }
.sig-mid    { display: table-cell; width: 42%; vertical-align: bottom; text-align: center; padding: 0 6pt; }
.sig-right  { display: table-cell; width: 38%; vertical-align: bottom; text-align: center; }

.qr-img { width: 44pt; height: 44pt; display: block; margin: 0 auto 2pt; }
.qr-lbl { font-family: Arial, Helvetica, sans-serif; font-size: 5.5pt; color: #9ca3af; }

.sig-note {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; color: #6b7280; margin-bottom: 4pt; line-height: 1.5;
}
.sig-line {
    border-top: 1pt solid #1F3A5F;
    padding-top: 2pt; margin-top: 12pt;
    display: block; width: 80%; margin-left: auto; margin-right: auto;
}
.sig-name { font-size: 8.5pt; font-weight: bold; color: #1F3A5F; }
.sig-title {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 5.5pt; color: #6b7280;
    letter-spacing: 0.75pt; text-transform: uppercase; margin-top: 1pt;
}

/* Right-side sig — full width of cell */
.sig-line-r {
    border-top: 1pt solid #1F3A5F;
    padding-top: 2pt; margin-top: 12pt;
    display: block; width: 90%; margin-left: auto; margin-right: auto;
}

/* ════════════════════════════════
   INLINE FOOTER (not fixed — hugs content)
   ════════════════════════════════ */
.foot {
    border-top: 0.75pt solid rgba(212,175,55,0.5);
    padding-top: 3pt;
    margin-top: 5pt;
    display: table; width: 100%;
}
.foot-l { display: table-cell; width: 60%; vertical-align: middle; }
.foot-r { display: table-cell; width: 40%; vertical-align: middle; text-align: right; }
.foot-txt {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 5.5pt; color: #9ca3af; line-height: 1.5;
}
.foot-rno { font-family: 'Courier New', monospace; font-size: 6pt; color: #6b7280; font-weight: bold; }
</style>
</head>
<body>
@php
use Carbon\Carbon;

$methodLabels = ['gcash'=>'GCash','maya'=>'Maya','cash'=>'Cash','bank'=>'Bank Transfer','card'=>'Card'];
$methodLabel  = $methodLabels[$payment->payment_method] ?? ucfirst($payment->payment_method);
$pmKey        = $payment->payment_method === 'paymaya' ? 'maya' : ($payment->payment_method ?? 'cash');
$methodClass  = 'chip-' . $pmKey;

$paidDate  = $payment->paid_at ?? $payment->created_at;
$txType    = $payment->transaction_type ?? 'debit';
$txClass   = $txType === 'credit' ? 'chip-credit' : 'chip-debit';

/* QR for self-verification */
$receiptUrl = config('app.url') . '/portal/payments/receipt/' . $payment->id;
$qrSvg  = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(100)->margin(0)->errorCorrection('H')
            ->generate($receiptUrl);
$qrB64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);

/* Amount in words */
if (!function_exists('amountInWords')) {
    function amountInWords(float $amount): string {
        $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                 'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                 'Seventeen','Eighteen','Nineteen'];
        $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        $n    = (int) $amount;
        $cts  = round(($amount - $n) * 100);
        if ($n === 0) return 'Zero Pesos Only';
        $w = '';
        if ($n >= 1000) { $w .= $ones[(int)($n/1000)] . ' Thousand '; $n %= 1000; }
        if ($n >= 100)  { $w .= $ones[(int)($n/100)]  . ' Hundred ';  $n %= 100;  }
        if ($n >= 20)   { $w .= $tens[(int)($n/10)]   . ' ';          $n %= 10;   }
        if ($n > 0)     { $w .= $ones[$n]              . ' ';                      }
        return trim($w) . ' Pesos' . ($cts > 0 ? ' and ' . $cts . '/100' : ' Only');
    }
}
@endphp

{{-- Borders wrap the content so they hug it, not the full A4 page --}}
<div class="border-outer">
<div class="border-inner">

{{-- Watermark sits behind everything (fixed, centered on rendered area) --}}
<div class="watermark">Official Receipt</div>

{{-- ─────────────────────── PAGE CONTENT ─────────────────────── --}}
<div class="page">

    {{-- ══ HEADER ══ --}}
    <div class="hdr">
        <div class="hdr-logo">
            <div class="logo-ring">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Parish Seal">
                @else
                    <svg width="50" height="50" viewBox="0 0 50 50" fill="none">
                        <circle cx="25" cy="25" r="23" stroke="#D4AF37" stroke-width="1.5"/>
                        <text x="25" y="31" text-anchor="middle" font-family="Georgia,serif" font-size="10" font-weight="bold" fill="#1F3A5F">MHC</text>
                    </svg>
                @endif
            </div>
        </div>
        <div class="hdr-center">
            <div class="diocese-bar">Diocese of San Pablo &nbsp;&nbsp;·&nbsp;&nbsp; Archdiocese of Lipa</div>
            <div class="parish-name">{{ $parish['name'] }}</div>
            <div class="parish-info">
                {{ $parish['address'] }}<br>
                Tel: {{ $parish['phone'] }} &nbsp;·&nbsp; {{ $parish['email'] }}
            </div>
        </div>
        <div class="hdr-box">
            <div class="or-box">
                <div class="or-tag">Official Receipt</div>
                <div class="or-num">{{ $payment->receipt_number }}</div>
                <div class="or-dt">{{ $paidDate->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    {{-- ══ TITLE ══ --}}
    <div class="title-wrap">
        <div class="title-main">Official Receipt</div>
        <div class="title-sub">Resibo ng Bayad &nbsp;·&nbsp; Acknowledgement of Payment</div>
    </div>
    <hr class="gold-rule">

    {{-- ══ PAYER / PAYMENT BAND ══ --}}
    <div class="info-band">
        <div class="info-left">
            <span class="band-label">Received From</span>
            <div class="payer-name">{{ $payment->parishioner->full_name }}</div>
            <div class="payer-detail">
                @if($payment->parishioner->address){{ $payment->parishioner->address }}@endif
                @if($payment->parishioner->barangay), Brgy. {{ $payment->parishioner->barangay }}@endif
                @if($payment->parishioner->city), {{ $payment->parishioner->city }}@endif
            </div>
            @php $tel = $payment->payer_contact ?? $payment->parishioner->contact_number ?? null; @endphp
            @if($tel)
            <div class="payer-detail">Tel: {{ $tel }}</div>
            @endif
        </div>
        <div class="info-right">
            <span class="band-label">Payment Status</span>
            <div><span class="chip chip-paid"><svg width="8" height="8" viewBox="0 0 12 12" fill="none" style="vertical-align:middle;margin-right:2pt;"><path d="M2 6l3 3 5-5" stroke="#065f46" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>PAID</span></div>

            <span class="band-label chip-spacing">Transaction Type</span>
            <div><span class="chip {{ $txClass }}">{{ strtoupper($txType) }}</span></div>

            <span class="band-label chip-spacing">Payment Method</span>
            <div><span class="chip {{ $methodClass }}">{{ $methodLabel }}</span></div>

            @if($payment->submitted_reference)
            <div class="ref-mono">Ref: {{ $payment->submitted_reference }}</div>
            @endif
        </div>
    </div>

    {{-- ══ ITEMS TABLE ══ --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th style="width:52%;">Description</th>
                <th style="width:22%;" class="r">Date</th>
                <th style="width:21%;" class="r">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="color:#9ca3af;font-family:Arial,sans-serif;font-size:8pt;">1</td>
                <td>
                    <div class="item-name">
                        @if($payment->booking)
                            {{ $payment->booking->getTypeLabel() }}
                        @elseif($payment->certificate)
                            {{ $payment->certificate->getTypeLabel() }} Certificate Fee
                        @else
                            Parish Service Payment
                        @endif
                    </div>
                    @if($payment->booking)
                    <div class="item-meta">
                        Booking Ref: {{ $payment->booking->reference_number }}
                        @if($payment->booking->scheduled_date)
                            &nbsp;·&nbsp; Scheduled: {{ $payment->booking->scheduled_date->format('F d, Y') }}
                            @if($payment->booking->scheduled_time)
                                {{ ' ' }}&nbsp;{{ \Carbon\Carbon::parse($payment->booking->scheduled_time)->format('g:i A') }}
                            @endif
                        @endif
                    </div>
                    @if($payment->notes)
                    <div class="item-meta">{{ $payment->notes }}</div>
                    @endif
                    @endif
                    @if($payment->certificate && !$payment->booking)
                    <div class="item-meta">Cert #: {{ $payment->certificate->certificate_number }}</div>
                    @endif
                </td>
                <td class="r" style="font-family:Arial,sans-serif;font-size:8pt;color:#6b7280;">
                    {{ $paidDate->format('M d, Y') }}<br>
                    <span style="font-size:7pt;">{{ $paidDate->format('g:i A') }}</span>
                </td>
                <td class="r" style="font-size:10.5pt;">
                    &#8369;{{ number_format($payment->amount, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- ══ TOTALS ══ --}}
    <table class="totals">
        <tr class="sub">
            <td style="width:57%;"></td>
            <td style="width:26%;">Subtotal</td>
            <td style="width:17%;text-align:right;">&#8369;{{ number_format($payment->amount, 2) }}</td>
        </tr>
        <tr class="sub">
            <td></td>
            <td>Tax / Fees</td>
            <td style="text-align:right;">&#8369;0.00</td>
        </tr>
        <tr class="grand">
            <td></td>
            <td>TOTAL AMOUNT PAID</td>
            <td>&#8369;{{ number_format($payment->amount, 2) }}</td>
        </tr>
    </table>

    {{-- ══ AMOUNT IN WORDS ══ --}}
    <div class="words-box">
        <div class="words-label">Amount in Words</div>
        <div class="words-text">{{ amountInWords((float)$payment->amount) }}</div>
    </div>

    {{-- ══ VERIFICATION STRIP ══ --}}
    <div class="verify">
        <div class="verify-l">
            <div class="v-label">Verify this receipt online at:</div>
            <div class="v-url">{{ $receiptUrl }}</div>
        </div>
        <div class="verify-r">
            <span class="v-rno-l">Receipt No.</span>
            <span class="v-rno">{{ $payment->receipt_number }}</span>
        </div>
    </div>

    {{-- ══ SIGNATURES + QR ══ --}}
    <div class="sig-row">

        {{-- QR code --}}
        <div class="sig-qr">
            <img src="{{ $qrB64 }}" alt="QR" class="qr-img">
            <div class="qr-lbl">Scan to verify online</div>
        </div>

        {{-- Issuing note + Parish Priest sig --}}
        <div class="sig-mid">
            <div class="sig-note">
                This is an official receipt of payment issued by<br>
                <strong style="color:#1F3A5F;font-size:7.5pt;">{{ $parish['name'] }}</strong>
            </div>
            <span class="sig-line">
                <div class="sig-name">{{ $parish['priest'] }}</div>
                <div class="sig-title">Parish Priest</div>
            </span>
        </div>

        {{-- Finance Officer sig --}}
        <div class="sig-right">
            <span class="sig-line-r">
                <div class="sig-name">
                    @php
                        $fo = \App\Models\Setting::get('parish_finance_officer', 'Finance Officer');
                    @endphp
                    {{ $fo }}
                </div>
                <div class="sig-title">Parish Treasurer &amp; Finance Officer</div>
            </span>
        </div>

    </div>

    {{-- ══ INLINE FOOTER (hugs content, no forced page-height) ══ --}}
    <div class="foot">
        <div class="foot-l">
            <span class="foot-txt">{{ $parish['name'] }} &nbsp;·&nbsp; {{ $parish['address'] }} &nbsp;·&nbsp; Tel: {{ $parish['phone'] }} &nbsp;·&nbsp; {{ $parish['email'] }}</span>
        </div>
        <div class="foot-r">
            <span class="foot-rno">{{ $payment->receipt_number }}</span><br>
            <span class="foot-txt">Issued: {{ $paidDate->format('F d, Y  g:i A') }}</span>
        </div>
    </div>

</div>{{-- /page --}}
</div>{{-- /border-inner --}}
</div>{{-- /border-outer --}}
</body>
</html>
