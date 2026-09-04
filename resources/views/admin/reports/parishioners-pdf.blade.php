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
   PAGE BORDER — blue theme
   ══════════════════════════════════════ */
.border-outer {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    border: 5pt solid #1d4ed8;
    background: transparent;
    z-index: 999;
}
.border-inner {
    position: fixed;
    top: 7pt; left: 7pt; right: 7pt; bottom: 7pt;
    border: 1pt solid #93c5fd;
    background: transparent;
    z-index: 998;
}
.border-thin {
    position: fixed;
    top: 10pt; left: 10pt; right: 10pt; bottom: 10pt;
    border: 0.5pt solid rgba(29,78,216,0.3);
    background: transparent;
    z-index: 997;
}

/* ══════════════════════════════════════
   CONTENT WRAPPER
   ══════════════════════════════════════ */
.page-content {
    position: relative;
    z-index: 1;
    padding: 16pt 20pt 14pt 20pt;
}

/* ── HEADER ── */
.hdr { width:100%; border-collapse:collapse; margin-bottom:7pt; padding-bottom:7pt; border-bottom:2pt solid #1d4ed8; }
.hdr td { vertical-align:middle; }
.hdr-logo { width:52pt; }
.hdr-logo img { width:48pt; height:48pt; border-radius:50%; border:2pt solid #d4af37; display:block; }
.hdr-center { text-align:center; }
.hdr-right { width:52pt; }
.parish-name { font-size:13pt; font-weight:bold; color:#1d4ed8; letter-spacing:0.3pt; }
.parish-sub { font-size:7pt; color:#6b7280; margin-top:1pt; }
.rpt-title { font-size:11pt; font-weight:bold; letter-spacing:2.5pt; text-transform:uppercase; color:#1e293b; margin-top:5pt; }
.rpt-meta  { font-size:7pt; color:#6b7280; margin-top:1pt; }

/* ── ACCENT BAR ── */
.accent-bar { height:2.5pt; background:linear-gradient(to right,#1d4ed8,#93c5fd,#1d4ed8); margin-bottom:8pt; }

/* ── STAT CARDS ── */
.cards { width:100%; border-collapse:collapse; margin:0 0 9pt 0; }
.cards td { text-align:center; padding:0 2.5pt; }
.card-inner { background:#eff6ff; border:0.75pt solid #bfdbfe; border-top:3pt solid #1d4ed8; padding:7pt 4pt 6pt; }
.card-inner.green  { background:#f0fdf4; border-color:#bbf7d0; border-top-color:#059669; }
.card-inner.red    { background:#fef2f2; border-color:#fecaca; border-top-color:#dc2626; }
.card-inner.purple { background:#f5f3ff; border-color:#ddd6fe; border-top-color:#7c3aed; }
.card-inner.amber  { background:#fffbeb; border-color:#fde68a; border-top-color:#b45309; }
.c-lbl { font-size:5.5pt; text-transform:uppercase; font-weight:bold; color:#64748b; letter-spacing:0.8pt; display:block; margin-bottom:3pt; }
.c-val { font-size:18pt; font-weight:bold; color:#1d4ed8; line-height:1; display:block; }
.c-val.green  { color:#059669; }
.c-val.red    { color:#dc2626; }
.c-val.purple { color:#7c3aed; }
.c-val.amber  { color:#b45309; }

/* ── SECTION HEADER ── */
.sec-hdr { background:#1d4ed8; color:#fff; font-size:8.5pt; font-weight:bold; letter-spacing:0.5pt; padding:4pt 8pt; margin:9pt 0 0; }

/* ── DATA TABLE ── */
table.dt { width:100%; border-collapse:collapse; }
table.dt thead tr { background:#1e3a8a; }
table.dt th { color:#fff; padding:4.5pt 6pt; font-size:8pt; font-weight:bold; }
table.dt td { padding:3.5pt 6pt; font-size:8.5pt; border-bottom:0.4pt solid #dbeafe; vertical-align:middle; }
table.dt tbody tr:nth-child(even) td { background:#eff6ff; }
table.dt tbody tr.total-row td { font-weight:bold; background:#dbeafe; border-top:1pt solid #1d4ed8; border-bottom:1pt solid #1d4ed8; }
.tr { text-align:right; }
.tc { text-align:center; }

/* ── 2-COL LAYOUT ── */
.two-col { width:100%; border-collapse:collapse; }
.two-col td { vertical-align:top; }
.col-l { width:50%; padding-right:5pt; }
.col-r { width:50%; padding-left:5pt; }

/* ── INFO BOX ── */
.info-box { background:#f0f9ff; border:0.75pt solid #bae6fd; border-left:3pt solid #1d4ed8; padding:8pt 10pt; margin-top:0; }
.info-row { font-size:8.5pt; color:#374151; margin-bottom:3pt; }
.info-row b { color:#1e293b; }

/* ── DIVIDER ── */
.divider { border:none; border-top:0.5pt solid #bfdbfe; margin:8pt 0; }

/* ── SIGNATURES ── */
.sig-wrap { width:100%; border-collapse:collapse; margin-top:14pt; }
.sig-wrap td { width:33.33%; text-align:center; padding:0 8pt; vertical-align:bottom; }
.sig-line { border-top:1pt solid #374151; margin:0 auto; width:86%; padding-top:4pt; font-size:8.5pt; font-weight:bold; color:#1e293b; }
.sig-role { font-size:7pt; color:#6b7280; margin-top:1.5pt; }

/* ── FOOTER ── */
.doc-footer { margin-top:9pt; padding-top:4pt; border-top:0.5pt solid #bfdbfe; width:100%; border-collapse:collapse; }
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
        <div class="rpt-title">Parishioner Report</div>
    </td>
    <td class="hdr-right"></td>
</tr></table>

<div class="accent-bar"></div>

{{-- STAT CARDS --}}
<table class="cards" cellpadding="0" cellspacing="0"><tr>
    <td><div class="card-inner"><span class="c-lbl">Total Registered</span><span class="c-val">{{ number_format($data['total']) }}</span></div></td>
    <td><div class="card-inner green"><span class="c-lbl">Active Members</span><span class="c-val green">{{ number_format($data['active']) }}</span></div></td>
    <td><div class="card-inner red"><span class="c-lbl">Inactive</span><span class="c-val red">{{ number_format($data['inactive']) }}</span></div></td>
    <td><div class="card-inner purple"><span class="c-lbl">Families</span><span class="c-val purple">{{ number_format($data['families']) }}</span></div></td>
    <td><div class="card-inner amber"><span class="c-lbl">New (Period)</span><span class="c-val amber">{{ number_format($data['new']) }}</span></div></td>
</tr></table>

{{-- GENDER + INFO BOX --}}
<table class="two-col" cellpadding="0" cellspacing="0"><tr>
    <td class="col-l">
        <div class="sec-hdr" style="margin-top:0;">Gender Breakdown</div>
        @php $gTotal = max($data['male']+$data['female']+$data['other'],1); @endphp
        <table class="dt" cellpadding="0" cellspacing="0">
            <thead><tr><th>Gender</th><th class="tr">Count</th><th class="tr">%</th></tr></thead>
            <tbody>
                <tr><td>Male</td><td class="tr">{{ number_format($data['male']) }}</td><td class="tr">{{ round($data['male']/$gTotal*100,1) }}%</td></tr>
                <tr><td>Female</td><td class="tr">{{ number_format($data['female']) }}</td><td class="tr">{{ round($data['female']/$gTotal*100,1) }}%</td></tr>
                <tr><td>Other / Unknown</td><td class="tr">{{ number_format($data['other']) }}</td><td class="tr">{{ round($data['other']/$gTotal*100,1) }}%</td></tr>
                <tr class="total-row"><td>TOTAL</td><td class="tr">{{ number_format($gTotal) }}</td><td class="tr">100%</td></tr>
            </tbody>
        </table>
    </td>
    <td class="col-r">
        <div class="sec-hdr" style="margin-top:0;">Registration Summary</div>
        <div class="info-box">
            <div class="info-row"><b>Parish:</b> {{ $parish['name'] }}</div>
            <div class="info-row"><b>Address:</b> {{ $parish['address'] }}</div>
            <div class="info-row"><b>Period:</b> {{ \Carbon\Carbon::parse($data['from'])->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($data['to'])->format('M d, Y') }}</div>
            <div class="info-row"><b>Total Registered:</b> {{ number_format($data['total']) }} parishioners</div>
            <div class="info-row"><b>Total Families:</b> {{ number_format($data['families']) }} households</div>
            <div class="info-row"><b>New Registrations:</b> {{ number_format($data['new']) }}</div>
            <div class="info-row"><b>Active Rate:</b> {{ $data['total']>0 ? round($data['active']/$data['total']*100,1) : 0 }}% of registered</div>
        </div>
    </td>
</tr></table>

{{-- TOP BARANGAYS --}}
@if($data['by_barangay']->count())
<div class="sec-hdr">Top Barangays by Registered Parishioners</div>
@php
    $chunks = $data['by_barangay']->chunk((int)ceil($data['by_barangay']->count()/2));
    $left   = $chunks->get(0, collect());
    $right  = $chunks->get(1, collect());
@endphp
<table class="two-col" cellpadding="0" cellspacing="0" style="margin-top:0;"><tr>
    <td class="col-l" style="padding-right:3pt;">
        <table class="dt" cellpadding="0" cellspacing="0">
            <thead><tr><th style="width:20pt;">#</th><th>Barangay</th><th class="tr">Count</th></tr></thead>
            <tbody>
                @foreach($left as $i => $row)
                <tr><td class="tc">{{ $i+1 }}</td><td>{{ $row->barangay ?? 'Unknown' }}</td><td class="tr">{{ number_format($row->total) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </td>
    <td class="col-r" style="padding-left:3pt;">
        <table class="dt" cellpadding="0" cellspacing="0">
            <thead><tr><th style="width:20pt;">#</th><th>Barangay</th><th class="tr">Count</th></tr></thead>
            <tbody>
                @foreach($right as $i => $row)
                <tr><td class="tc">{{ $left->count()+$i+1 }}</td><td>{{ $row->barangay ?? 'Unknown' }}</td><td class="tr">{{ number_format($row->total) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </td>
</tr></table>
@endif

<hr class="divider">

{{-- PERIOD / PRINTED — flows naturally at bottom of content --}}
<table style="width:100%;border-collapse:collapse;margin-top:10pt;padding-top:5pt;border-top:0.5pt solid #bfdbfe;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="font-size:7.5pt;color:#374151;">{{ $parish['name'] }} &middot; Parishioner Report &middot; Confidential</td>
        <td style="font-size:7.5pt;color:#374151;text-align:right;">
            Period: {{ \Carbon\Carbon::parse($data['from'])->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($data['to'])->format('M d, Y') }}
            &nbsp;|&nbsp; Printed: {{ $printedAt }}
        </td>
    </tr>
</table>

{{-- SIGNATURES --}}
<table class="sig-wrap" cellpadding="0" cellspacing="0"><tr>
    <td><div class="sig-line">Prepared by</div><div class="sig-role">Parish Secretary</div></td>
    <td><div class="sig-line">Reviewed by</div><div class="sig-role">Finance Officer</div></td>
    <td><div class="sig-line">{{ config('parish.priest') }}</div><div class="sig-role">Parish Priest</div></td>
</tr></table>

{{-- FOOTER --}}
<table class="doc-footer" cellpadding="0" cellspacing="0"><tr>
    <td>{{ $parish['name'] }} &middot; Parishioner Report &middot; Confidential</td>
    <td style="text-align:right;">Printed: {{ $printedAt }}</td>
</tr></table>

</div>{{-- /.page-content --}}

</body>
</html>
