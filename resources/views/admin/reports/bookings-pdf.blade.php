<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
@page { size: A4 portrait; margin: 15mm 15mm 18mm 15mm; }
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; color: #1a1a2e; line-height:1.4; background:#fff; }

/* ══════════════════════════════════════
   PAGE BORDER — position:fixed overlays
   (same technique as certificates)
   ══════════════════════════════════════ */
.border-outer {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    border: 5pt solid #b45309;
    background: transparent;
    z-index: 999;
}
.border-inner {
    position: fixed;
    top: 7pt; left: 7pt; right: 7pt; bottom: 7pt;
    border: 1pt solid #fbbf24;
    background: transparent;
    z-index: 998;
}
.border-thin {
    position: fixed;
    top: 10pt; left: 10pt; right: 10pt; bottom: 10pt;
    border: 0.5pt solid rgba(180,83,9,0.35);
    background: transparent;
    z-index: 997;
}

/* ══════════════════════════════════════
   CONTENT WRAPPER — padded inside border
   ══════════════════════════════════════ */
.page-content {
    position: relative;
    z-index: 1;
    padding: 16pt 20pt 14pt 20pt;
}

/* ── HEADER ── */
.hdr { width:100%; border-collapse:collapse; margin-bottom:7pt; padding-bottom:7pt; border-bottom:2pt solid #b45309; }
.hdr td { vertical-align:middle; }
.hdr-logo { width:52pt; }
.hdr-logo img { width:48pt; height:48pt; border-radius:50%; border:2pt solid #d4af37; display:block; }
.hdr-center { text-align:center; }
.hdr-right { width:52pt; }
.parish-name { font-size:13pt; font-weight:bold; color:#b45309; letter-spacing:0.3pt; }
.parish-sub { font-size:7pt; color:#6b7280; margin-top:1pt; }
.rpt-title { font-size:11pt; font-weight:bold; letter-spacing:2.5pt; text-transform:uppercase; color:#1e293b; margin-top:5pt; }
.rpt-meta  { font-size:7pt; color:#6b7280; margin-top:1pt; }

/* ── ACCENT BAR ── */
.accent-bar { height:2.5pt; background:linear-gradient(to right,#b45309,#fbbf24,#b45309); margin-bottom:8pt; }

/* ── STAT CARDS ── */
.cards { width:100%; border-collapse:collapse; margin:0 0 9pt 0; }
.cards td { text-align:center; padding:0 2.5pt; }
.card-inner { background:#fffbeb; border:0.75pt solid #fde68a; border-top:3pt solid #b45309; padding:7pt 4pt 6pt; }
.card-inner.green  { background:#f0fdf4; border-color:#bbf7d0; border-top-color:#059669; }
.card-inner.blue   { background:#eff6ff; border-color:#bfdbfe; border-top-color:#1d4ed8; }
.card-inner.red    { background:#fef2f2; border-color:#fecaca; border-top-color:#dc2626; }
.card-inner.purple { background:#f5f3ff; border-color:#ddd6fe; border-top-color:#7c3aed; }
.c-lbl { font-size:5.5pt; text-transform:uppercase; font-weight:bold; color:#64748b; letter-spacing:0.8pt; display:block; margin-bottom:3pt; }
.c-val { font-size:18pt; font-weight:bold; color:#b45309; line-height:1; display:block; }
.c-val.green  { color:#059669; }
.c-val.blue   { color:#1d4ed8; }
.c-val.red    { color:#dc2626; }
.c-val.purple { color:#7c3aed; }

/* ── SECTION HEADER ── */
.sec-hdr { background:#b45309; color:#fff; font-size:8.5pt; font-weight:bold; letter-spacing:0.5pt; padding:4pt 8pt; margin:9pt 0 0; }

/* ── DATA TABLE ── */
table.dt { width:100%; border-collapse:collapse; }
table.dt thead tr { background:#92400e; }
table.dt th { color:#fff; padding:4.5pt 6pt; font-size:8pt; font-weight:bold; letter-spacing:0.3pt; }
table.dt td { padding:3.5pt 6pt; font-size:8.5pt; border-bottom:0.4pt solid #fef3c7; }
table.dt tbody tr:nth-child(even) td { background:#fffbeb; }
table.dt tbody tr.total-row td { font-weight:bold; background:#fef3c7; border-top:1pt solid #b45309; border-bottom:1pt solid #b45309; }
.tr { text-align:right; }
.tc { text-align:center; }

/* ── REVENUE BOX ── */
.rev-box { background:#b45309; color:#fff; padding:9pt 14pt; margin:9pt 0; }
.rev-lbl { font-size:7pt; font-weight:bold; text-transform:uppercase; letter-spacing:1pt; opacity:0.85; display:block; }
.rev-val { font-size:19pt; font-weight:bold; display:block; margin-top:2pt; }
.rev-sub { font-size:7pt; opacity:0.75; display:block; margin-top:1pt; }

/* ── DIVIDER ── */
.divider { border:none; border-top:0.5pt solid #fde68a; margin:8pt 0; }

/* ── SIGNATURES ── */
.sig-wrap { width:100%; border-collapse:collapse; margin-top:14pt; }
.sig-wrap td { width:33.33%; text-align:center; padding:0 8pt; vertical-align:bottom; }
.sig-line { border-top:1pt solid #374151; margin:0 auto; width:86%; padding-top:4pt; font-size:8.5pt; font-weight:bold; color:#1e293b; }
.sig-role { font-size:7pt; color:#6b7280; margin-top:1.5pt; }

/* ── FOOTER ── */
.doc-footer { margin-top:9pt; padding-top:4pt; border-top:0.5pt solid #fde68a; width:100%; border-collapse:collapse; }
.doc-footer td { font-size:6.5pt; color:#9ca3af; }

/* ── PAGE BREAK RULES ── */
table.dt { page-break-inside: auto; }
table.dt tr { page-break-inside: avoid; break-inside: avoid; }
table.dt thead { display: table-header-group; }
table.dt tfoot { display: table-footer-group; }
.sig-wrap { page-break-inside: avoid; break-inside: avoid; }
.doc-footer { page-break-inside: avoid; break-inside: avoid; }
</style>
</head>
<body>

{{-- PAGE BORDER OVERLAYS --}}
<div class="border-outer"></div>
<div class="border-inner"></div>
<div class="border-thin"></div>

<div class="page-content">

{{-- HEADER --}}
<table class="hdr" cellpadding="0" cellspacing="0"><tr>
    <td class="hdr-logo">@if(file_exists($logoPath))<img src="{{ $logoPath }}" alt="Logo">@endif</td>
    <td class="hdr-center">
        <div class="parish-name">{{ $parish['name'] }}</div>
        <div class="parish-sub">{{ $parish['address'] }} &nbsp;&middot;&nbsp; {{ $parish['phone'] }}</div>
        <div class="rpt-title">Booking Report</div>
    </td>
    <td class="hdr-right"></td>
</tr></table>

<div class="accent-bar"></div>

{{-- STAT CARDS --}}
<table class="cards" cellpadding="0" cellspacing="0"><tr>
    <td><div class="card-inner"><span class="c-lbl">Total</span><span class="c-val">{{ number_format($data['total']) }}</span></div></td>
    <td><div class="card-inner purple"><span class="c-lbl">Pending</span><span class="c-val purple">{{ number_format($data['pending']) }}</span></div></td>
    <td><div class="card-inner green"><span class="c-lbl">Confirmed</span><span class="c-val green">{{ number_format($data['confirmed']) }}</span></div></td>
    <td><div class="card-inner blue"><span class="c-lbl">Completed</span><span class="c-val blue">{{ number_format($data['completed']) }}</span></div></td>
    <td><div class="card-inner red"><span class="c-lbl">Cancelled</span><span class="c-val red">{{ number_format($data['cancelled']) }}</span></div></td>
</tr></table>

{{-- BOOKINGS BY TYPE --}}
<div class="sec-hdr">Bookings by Service Type</div>
<table class="dt" cellpadding="0" cellspacing="0">
    <thead><tr>
        <th style="width:65%;">Service Type</th>
        <th class="tr">Count</th>
        <th class="tr">% Share</th>
    </tr></thead>
    <tbody>
        @php $gtotal = max($data['total'], 1); @endphp
        @foreach($data['by_type'] as $t)
        <tr>
            <td>{{ $t['type'] }}</td>
            <td class="tr">{{ number_format($t['total']) }}</td>
            <td class="tr">{{ round($t['total']/$gtotal*100,1) }}%</td>
        </tr>
        @endforeach
        <tr class="total-row"><td>TOTAL</td><td class="tr">{{ number_format($data['total']) }}</td><td class="tr">100%</td></tr>
    </tbody>
</table>

{{-- REVENUE --}}
<div class="rev-box">
    <span class="rev-lbl">Revenue from Completed Bookings (Period)</span>
    <span class="rev-val">&#8369;{{ number_format($data['revenue'], 2) }}</span>
    <span class="rev-sub">Based on {{ number_format($data['completed']) }} completed booking(s) within the selected period</span>
</div>

{{-- STATUS BREAKDOWN --}}
<div class="sec-hdr">Status Breakdown</div>
<table class="dt" cellpadding="0" cellspacing="0">
    <thead><tr>
        <th>Status</th>
        <th class="tr">Count</th>
        <th class="tr">% of Total</th>
    </tr></thead>
    <tbody>
        @php
        $statuses = ['Pending'=>$data['pending'],'Confirmed'=>$data['confirmed'],'Completed'=>$data['completed'],'Cancelled'=>$data['cancelled']];
        @endphp
        @foreach($statuses as $label => $count)
        <tr>
            <td>{{ $label }}</td>
            <td class="tr">{{ number_format($count) }}</td>
            <td class="tr">{{ $data['total'] > 0 ? round($count/$data['total']*100,1) : 0 }}%</td>
        </tr>
        @endforeach
        <tr class="total-row"><td>TOTAL</td><td class="tr">{{ number_format($data['total']) }}</td><td class="tr">100%</td></tr>
    </tbody>
</table>

<hr class="divider">

{{-- PERIOD / PRINTED — flows naturally at bottom of content --}}
<table style="width:100%;border-collapse:collapse;margin-top:10pt;padding-top:5pt;border-top:0.5pt solid #fde68a;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="font-size:7.5pt;color:#374151;">{{ $parish['name'] }} &middot; Booking Report &middot; Confidential</td>
        <td style="font-size:7.5pt;color:#374151;text-align:right;">
            Period: {{ \Carbon\Carbon::parse($data['from'])->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($data['to'])->format('M d, Y') }}
            &nbsp;|&nbsp; Printed: {{ $printedAt }}
        </td>
    </tr>
</table>

{{-- SIGNATURES --}}
<table class="sig-wrap" cellpadding="0" cellspacing="0"><tr>
    <td><div class="sig-line">Prepared by</div><div class="sig-role">Parish Secretary</div></td>
    <td><div class="sig-line">Verified by</div><div class="sig-role">Finance Officer</div></td>
    <td><div class="sig-line">{{ config('parish.priest') }}</div><div class="sig-role">Parish Priest</div></td>
</tr></table>

{{-- FOOTER --}}
<table class="doc-footer" cellpadding="0" cellspacing="0"><tr>
    <td style="text-align:center;">
        &copy; {{ date('Y') }} {{ $parish['name'] }} &mdash; All rights reserved.
    </td>
</tr></table>

</div>{{-- /.page-content --}}

</body>
</html>
