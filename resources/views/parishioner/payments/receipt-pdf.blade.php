<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
/* ═══════════════════════════════════════════════════════════════
   OFFICIAL PAYMENT RECEIPT — A4 Portrait
   Mary Help of Christians Parish
   Engine: DomPDF v2
   Navy #1F3A5F · Gold #D4AF37
   ═══════════════════════════════════════════════════════════════ */

/*
   PAGE SETUP
   ----------
   @page margin defines the DomPDF "canvas" inside the physical A4 sheet.
   position:fixed elements are anchored to THIS canvas (top:0 = top of margin,
   bottom:0 = bottom of margin). So the border frames span the full canvas
   height and align perfectly on all four sides.
   body padding keeps content clear of the border strokes.
*/
@page {
    size: A4 portrait;
    margin: 8mm;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    margin: 0; padding: 0;
    background: #fff;
    /* DejaVu Sans is bundled with DomPDF and contains the ₱ glyph (U+20B1).
       We list it first so currency characters render correctly. */
    font-family: 'DejaVu Sans', 'Times New Roman', Georgia, serif;
    color: #1a1a2e;
    font-size: 9pt;
    line-height: 1.35;
}

/* ── Body padding keeps content inside the border strokes ── */
body { padding: 6pt 7pt; }

/* ═══════════════════════════════════════════════
   DECORATIVE BORDER FRAMES
   position:fixed → DomPDF anchors to @page canvas
   (top:0 = top of 8mm margin, bottom:0 = bottom of 8mm margin)
   These extend evenly on all four sides at every page height.
   ═══════════════════════════════════════════════ */
.border-gold {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    border: 2.5pt solid #D4AF37;
    z-index: 0;
    pointer-events: none;
}
.border-navy {
    position: fixed;
    top: 3pt; left: 3pt; right: 3pt; bottom: 3pt;
    border: 0.75pt solid #1F3A5F;
    z-index: 0;
    pointer-events: none;
}

/* ── Diagonal watermark ── */
.watermark {
    position: fixed;
    top: 40%; left: 50%;
    transform: translate(-50%, -50%) rotate(-35deg);
    font-size: 48pt;
    color: rgba(31,58,95,0.05);
    font-weight: 900;
    white-space: nowrap;
    pointer-events: none;
    z-index: 0;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    letter-spacing: 5pt;
    text-transform: uppercase;
}

/* All real content above the fixed overlays */
.page { position: relative; z-index: 1; }

/* ════════════════════════════════
   HEADER
   ════════════════════════════════ */
.hdr {
    display: table;
    width: 100%;
    border-collapse: collapse;
    padding-bottom: 6pt;
    margin-bottom: 5pt;
    border-bottom: 2pt solid #D4AF37;
}
.hdr-logo   { display: table-cell; width: 13%; vertical-align: middle; }
.hdr-center { display: table-cell; width: 67%; vertical-align: middle; text-align: center; padding: 0 5pt; }
.hdr-box    { display: table-cell; width: 20%; vertical-align: middle; text-align: right; }

.logo-ring {
    width: 48pt; height: 48pt; border-radius: 50%;
    border: 2pt solid #D4AF37;
    overflow: hidden; display: block;
    background: #fff; text-align: center; line-height: 48pt;
}
.logo-ring img { width: 48pt; height: 48pt; display: block; }

.diocese-bar {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6pt; color: #D4AF37;
    letter-spacing: 2pt; text-transform: uppercase;
    margin-bottom: 2pt;
}
.parish-name {
    font-size: 13pt; font-weight: bold; color: #1F3A5F;
    letter-spacing: 0.5pt; line-height: 1.2; margin-bottom: 2pt;
}
.parish-info {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6pt; color: #6b7280; line-height: 1.6;
}

/* OR box — top-right */
.or-box {
    display: inline-block;
    border: 1.5pt solid #1F3A5F; border-radius: 4pt;
    padding: 4pt 5pt; min-width: 80pt; text-align: center;
}
.or-tag {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5.5pt; font-weight: bold;
    letter-spacing: 1.5pt; text-transform: uppercase;
    color: #6b7280; margin-bottom: 2pt;
}
.or-num {
    font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
    font-size: 8pt; font-weight: bold; color: #1F3A5F;
}
.or-dt {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6pt; color: #9ca3af; margin-top: 1.5pt;
}

/* ════════════════════════════════
   DOCUMENT TITLE
   ════════════════════════════════ */
.title-wrap { text-align: center; margin: 4pt 0 4pt; }
.title-main {
    font-size: 17pt; font-weight: bold; color: #1F3A5F;
    letter-spacing: 4pt; text-transform: uppercase;
    line-height: 1;
}
.title-sub {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.5pt; color: #D4AF37;
    letter-spacing: 2pt; text-transform: uppercase;
    margin-top: 2pt;
}
.gold-rule { border: none; border-top: 1pt solid #D4AF37; margin: 3pt 0; }

/* ════════════════════════════════
   PAYER / PAYMENT INFO BAND
   ════════════════════════════════ */
.info-band {
    background: #f8faff;
    border: 1pt solid #e0e7ff;
    border-radius: 4pt;
    padding: 5pt 7pt;
    margin-bottom: 4pt;
    display: table;
    width: 100%;
}
.info-left  { display: table-cell; width: 55%; vertical-align: top; }
.info-right { display: table-cell; width: 45%; vertical-align: top; text-align: right; }

.band-label {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5.5pt; font-weight: bold;
    letter-spacing: 1.5pt; text-transform: uppercase;
    color: #D4AF37; display: block; margin-bottom: 1.5pt;
}
.payer-name { font-size: 10.5pt; font-weight: bold; color: #1F3A5F; }
.payer-detail {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.5pt; color: #6b7280; margin-top: 1.5pt; line-height: 1.5;
}

/* status/method chips */
.chip {
    display: inline-block; border-radius: 20pt;
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 7pt; font-weight: bold;
    padding: 2pt 6pt; letter-spacing: 0.5pt;
}
.chip-paid    { background: #d1fae5; color: #065f46; border: 1pt solid #6ee7b7; }
.chip-debit   { background: #fee2e2; color: #991b1b; }
.chip-credit  { background: #d1fae5; color: #065f46; }
.chip-gcash   { background: #eff6ff; color: #1d4ed8; border: 0.75pt solid #bfdbfe; }
.chip-maya    { background: #f0fdf4; color: #166534; border: 0.75pt solid #bbf7d0; }
.chip-cash    { background: #fffbeb; color: #92400e; border: 0.75pt solid #fde68a; }
.chip-bank    { background: #f3f4f6; color: #374151; border: 0.75pt solid #d1d5db; }
.chip-card    { background: #eef2ff; color: #4338ca; border: 0.75pt solid #c7d2fe; }
.chip-spacing { margin-top: 3pt; }

.ref-mono {
    font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
    font-size: 6pt; color: #6b7280; margin-top: 2pt;
}

/* ════════════════════════════════
   ITEMS TABLE
   ════════════════════════════════ */
.items { width: 100%; border-collapse: collapse; }
.items thead tr { background: #1F3A5F; }
.items thead th {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.5pt; font-weight: bold;
    letter-spacing: 1pt; text-transform: uppercase;
    color: #fff; padding: 4pt 5pt; text-align: left;
}
.items thead th.r { text-align: right; }
.items tbody tr { border-bottom: 0.5pt solid #f0f0f0; }
.items tbody tr:last-child { border-bottom: none; }
.items tbody td { padding: 5pt 5pt; vertical-align: top; }
.items tbody td.r { text-align: right; font-weight: bold; color: #1F3A5F; }
.item-name { font-weight: bold; color: #1F3A5F; font-size: 9.5pt; }
.item-meta {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6pt; color: #9ca3af; margin-top: 2pt; line-height: 1.5;
}

/* ── Currency class: DejaVu Sans contains ₱ (U+20B1) ── */
.peso {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-weight: bold;
}

/* ════════════════════════════════
   TOTALS
   ════════════════════════════════ */
.totals { width: 100%; border-collapse: collapse; }
.totals td {
    padding: 2pt 5pt;
    font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 8pt;
}
.totals .sub td { color: #6b7280; }
.totals .sub td:last-child { text-align: right; }
.totals .grand { background: #1F3A5F; }
.totals .grand td { color: #fff; font-weight: bold; font-size: 10pt; padding: 4pt 5pt; }
.totals .grand td:last-child { text-align: right; font-size: 12.5pt; }

/* ════════════════════════════════
   AMOUNT IN WORDS
   ════════════════════════════════ */
.words-box {
    background: #fffdf0;
    border: 0.75pt solid #fde68a;
    border-radius: 3pt;
    padding: 3pt 6pt;
    margin: 3pt 0;
}
.words-label {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5.5pt; font-weight: bold;
    color: #D4AF37; text-transform: uppercase; letter-spacing: 1pt;
}
.words-text {
    font-size: 8.5pt; font-style: italic; color: #1F3A5F; font-weight: bold;
    margin-top: 0.5pt;
}

/* ════════════════════════════════
   VERIFY STRIP
   ════════════════════════════════ */
.verify {
    background: #1F3A5F; border-radius: 3pt;
    padding: 3pt 6pt; margin: 3pt 0;
    display: table; width: 100%;
}
.verify-l { display: table-cell; vertical-align: middle; width: 72%; }
.verify-r { display: table-cell; vertical-align: middle; text-align: right; width: 28%; }
.v-label  { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 6pt; color: rgba(255,255,255,0.75); }
.v-url    { font-family: 'DejaVu Sans Mono', 'Courier New', monospace; font-size: 5.5pt; color: #93c5fd; margin-top: 1pt; }
.v-rno    { font-family: 'DejaVu Sans Mono', 'Courier New', monospace; font-size: 7.5pt; color: #fff; font-weight: bold; }
.v-rno-l  { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 5.5pt; color: rgba(255,255,255,0.6); display: block; margin-bottom: 1pt; }

/* ════════════════════════════════
   SIGNATURES + QR ROW
   ════════════════════════════════ */
.sig-row {
    display: table; width: 100%;
    border-top: 1pt solid #e5e7eb;
    padding-top: 5pt; margin-top: 4pt;
}
.sig-qr    { display: table-cell; width: 20%; vertical-align: bottom; text-align: center; }
.sig-mid   { display: table-cell; width: 42%; vertical-align: bottom; text-align: center; padding: 0 5pt; }
.sig-right { display: table-cell; width: 38%; vertical-align: bottom; text-align: center; }

.qr-img { width: 42pt; height: 42pt; display: block; margin: 0 auto 2pt; }
.qr-lbl { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 5.5pt; color: #9ca3af; }

.sig-note {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 6.5pt; color: #6b7280; margin-bottom: 4pt; line-height: 1.5;
}
.sig-line {
    border-top: 1pt solid #1F3A5F;
    padding-top: 2pt; margin-top: 8pt;
    display: block; width: 85%; margin-left: auto; margin-right: auto;
}
.sig-name { font-size: 8pt; font-weight: bold; color: #1F3A5F; }
.sig-title {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5.5pt; color: #6b7280;
    letter-spacing: 0.75pt; text-transform: uppercase; margin-top: 1pt;
}
.sig-line-r {
    border-top: 1pt solid #1F3A5F;
    padding-top: 2pt; margin-top: 8pt;
    display: block; width: 90%; margin-left: auto; margin-right: auto;
}

/* ════════════════════════════════
   INLINE FOOTER
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
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 5pt; color: #9ca3af; line-height: 1.5;
}
.foot-rno { font-family: 'DejaVu Sans Mono', 'Courier New', monospace; font-size: 5.5pt; color: #6b7280; font-weight: bold; }
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

/* QR code for receipt verification */
$receiptUrl = config('app.url') . '/portal/payments/receipt/' . $payment->id;
$qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
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
        if ($n > 0)     { $w .= $ones[$n]              . ' '; }
        return trim($w) . ' Pesos' . ($cts > 0 ? ' and ' . $cts . '/100' : ' Only');
    }
}

/* Finance Officer name from settings */
$fo = \App\Models\Setting::get('parish_finance_officer', 'Finance Officer');

/* PHP peso sign — use the actual UTF-8 character rather than the HTML entity
   &#8369; (U+20B1) so DomPDF can render it via DejaVu Sans. */
$P = "\xe2\x82\xb1";   // UTF-8 bytes for ₱
@endphp

{{-- ─── Fixed full-canvas border frames ───
     position:fixed in DomPDF = anchored to @page content area.
     top:0/left:0/right:0/bottom:0 = exactly the 8mm-margin canvas.
     These align perfectly on all four sides at every page height. --}}
<div class="border-gold"></div>
<div class="border-navy"></div>
<div class="watermark">Official Receipt</div>

{{-- ─── Page content (padding on body keeps it inside borders) ─── --}}
<div class="page">

    {{-- ══ HEADER ══ --}}
    <div class="hdr">
        <div class="hdr-logo">
            <div class="logo-ring">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Parish Seal">
                @else
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                        <circle cx="24" cy="24" r="22" stroke="#D4AF37" stroke-width="1.5"/>
                        <text x="24" y="29" text-anchor="middle" font-family="Georgia,serif" font-size="9" font-weight="bold" fill="#1F3A5F">MHC</text>
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

    {{-- ══ PAYER / PAYMENT INFO BAND ══ --}}
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
            <div>
                <span class="chip chip-paid">
                    {{-- SVG checkmark — DomPDF renders SVG reliably regardless of font --}}
                    <svg width="8" height="8" viewBox="0 0 12 12" fill="none"
                         style="vertical-align:middle;margin-right:2pt;">
                        <path d="M2 6l3 3 5-5" stroke="#065f46" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>PAID
                </span>
            </div>

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
                <td style="color:#9ca3af;font-family:'DejaVu Sans',Arial,sans-serif;font-size:8pt;">1</td>
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
                                &nbsp;{{ \Carbon\Carbon::parse($payment->booking->scheduled_time)->format('g:i A') }}
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
                <td class="r" style="font-family:'DejaVu Sans',Arial,sans-serif;font-size:7.5pt;color:#6b7280;">
                    {{ $paidDate->format('M d, Y') }}<br>
                    {{ $paidDate->format('g:i A') }}
                </td>
                {{-- Use $P (UTF-8 ₱) with DejaVu Sans class — renders correctly in DomPDF --}}
                <td class="r"><span class="peso">{{ $P }}{{ number_format($payment->amount, 2) }}</span></td>
            </tr>
        </tbody>
    </table>

    {{-- ══ TOTALS ══ --}}
    <table class="totals">
        <tr class="sub">
            <td style="width:57%;"></td>
            <td style="width:26%;">Subtotal</td>
            <td style="width:17%;text-align:right;"><span class="peso">{{ $P }}{{ number_format($payment->amount, 2) }}</span></td>
        </tr>
        <tr class="sub">
            <td></td>
            <td>Tax / Fees</td>
            <td style="text-align:right;"><span class="peso">{{ $P }}0.00</span></td>
        </tr>
        <tr class="grand">
            <td></td>
            <td>TOTAL AMOUNT PAID</td>
            <td><span class="peso">{{ $P }}{{ number_format($payment->amount, 2) }}</span></td>
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
                <div class="sig-name">{{ $fo }}</div>
                <div class="sig-title">Parish Treasurer &amp; Finance Officer</div>
            </span>
        </div>

    </div>

    {{-- ══ INLINE FOOTER ══ --}}
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
</body>
</html>
