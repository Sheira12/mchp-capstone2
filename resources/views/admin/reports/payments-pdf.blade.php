<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
@page { size: A4 portrait; margin: 15mm 15mm 22mm 15mm; }
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; color: #1a1a2e; line-height:1.4; background:#fff; }

/* ══════════════════════════════════════
   PAGE BORDER — green theme
   ══════════════════════════════════════ */
.border-outer {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    border: 5pt solid #059669;
    background: transparent;
    z-index: 999;
}
.border-inner {
    position: fixed;
    top: 7pt; left: 7pt; right: 7pt; bottom: 7pt;
    border: 1pt solid #6ee7b7;
    background: transparent;
    z-index: 998;
}
.border-thin {
    position: fixed;
    top: 10pt; left: 10pt; right: 10pt; bottom: 10pt;
    border: 0.5pt solid rgba(5,150,105,0.3);
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
.hdr { width:100%; border-collapse:collapse; margin-bottom:7pt; padding-bottom:7pt; border-bottom:2pt solid #059669; }
.hdr td { vertical-align:middle; }
.hdr-logo { width:52pt; }
.hdr-logo img { width:48pt; height:48pt; border-radius:50%; border:2pt solid #d4af37; display:block; }
.hdr-center { text-align:center; }
.hdr-right { width:52pt; }
.parish-name { font-size:13pt; font-weight:bold; color:#059669; letter-spacing:0.3pt; }
.parish-sub { font-size:7pt; color:#6b7280; margin-top:1pt; }
.rpt-title { font-size:11pt; font-weight:bold; letter-spacing:2.5pt; text-transform:uppercase; color:#1e293b; margin-top:5pt; }
.rpt-meta  { font-size:7pt; color:#6b7280; margin-top:1pt; }

/* ── ACCENT BAR ── */
.accent-bar { height:2.5pt; background:linear-gradient(to right,#059669,#6ee7b7,#059669); margin-bottom:8pt; }

/* ── STAT CARDS ── */
.cards { width:100%; border-collapse:collapse; margin:0 0 9pt 0; }
.cards td { text-align:center; padding:0 2.5pt; }
.card-inner { background:#f0fdf4; border:0.75pt solid #bbf7d0; border-top:3pt solid #059669; padding:7pt 4pt 6pt; }
.card-inner.amber { background:#fffbeb; border-color:#fde68a; border-top-color:#b45309; }
.card-inner.red   { background:#fef2f2; border-color:#fecaca; border-top-color:#dc2626; }
.card-inner.blue  { background:#eff6ff; border-color:#bfdbfe; border-top-color:#1d4ed8; }
.c-lbl { font-size:5.5pt; text-transform:uppercase; font-weight:bold; color:#64748b; letter-spacing:0.8pt; display:block; margin-bottom:3pt; }
.c-val { font-size:16pt; font-weight:bold; color:#059669; line-height:1; display:block; }
.c-val.amber { color:#b45309; }
.c-val.red   { color:#dc2626; }
.c-val.blue  { color:#1d4ed8; }

/* ── SECTION HEADER ── */
.sec-hdr { background:#059669; color:#fff; font-size:8.5pt; font-weight:bold; letter-spacing:0.5pt; padding:4pt 8pt; margin:9pt 0 0; }

/* ── DATA TABLE ── */
table.dt { width:100%; border-collapse:collapse; }
table.dt thead tr { background:#065f46; }
table.dt th { color:#fff; padding:4.5pt 6pt; font-size:8pt; font-weight:bold; }
table.dt td { padding:3.5pt 6pt; font-size:8.5pt; border-bottom:0.4pt solid #dcfce7; }
table.dt tbody tr:nth-child(even) td { background:#f0fdf4; }
table.dt tbody tr.total-row td { font-weight:bold; background:#dcfce7; border-top:1pt solid #059669; border-bottom:1pt solid #059669; }
.tr { text-align:right; }
.tc { text-align:center; }

/* ── HIGHLIGHT BOXES ── */
.hl-tbl { width:100%; border-collapse:collapse; margin:9pt 0; }
.hl-tbl td { padding:0 3pt; }
.hl-inner { padding:9pt 12pt; }
.hl-lbl { font-size:7pt; text-transform:uppercase; font-weight:bold; letter-spacing:0.8pt; display:block; margin-bottom:3pt; }
.hl-val { font-size:17pt; font-weight:bold; line-height:1; display:block; }
.hl-sub { font-size:7pt; display:block; margin-top:2pt; opacity:0.85; }

/* ── DIVIDER ── */
.divider { border:none; border-top:0.5pt solid #bbf7d0; margin:8pt 0; }

/* ── SIGNATURES ── */
.sig-wrap { width:100%; border-collapse:collapse; margin-top:14pt; }
.sig-wrap td { width:33.33%; text-align:center; padding:0 8pt; vertical-align:bottom; }
.sig-line { border-top:1pt solid #374151; margin:0 auto; width:86%; padding-top:4pt; font-size:8.5pt; font-weight:bold; color:#1e293b; }
.sig-role { font-size:7pt; color:#6b7280; margin-top:1.5pt; }

/* ── FOOTER ── */
.doc-footer { margin-top:9pt; padding-top:4pt; border-top:0.5pt solid #bbf7d0; width:100%; border-collapse:collapse; }
.doc-footer td { font-size:6.5pt; color:#9ca3af; }

/* ── PAGE BREAK RULES ── */
table.dt { page-break-inside: auto; }
table.dt tr { page-break-inside: avoid; break-inside: avoid; }
table.dt thead { display: table-header-group; }
table.dt tfoot { display: table-footer-group; }
.sig-wrap { page-break-inside: avoid; break-inside: avoid; }
.doc-footer { page-break-inside: avoid; break-inside: avoid; }

/* ── COPYRIGHT FOOTER ── */
.page-copyright {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    text-align: center;
    font-size: 6.5pt;
    color: #9ca3af;
    border-top: 0.5pt solid #bbf7d0;
    padding: 3pt 15mm;
    background: #fff;
}
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
        <div class="rpt-title">Payment Report{{ !empty($data['quarter_label']) ? ' — ' . $data['quarter_label'] : '' }}</div>
    </td>
    <td class="hdr-right"></td>
</tr></table>

<div class="accent-bar"></div>

{{-- STAT CARDS --}}
<table class="cards" cellpadding="0" cellspacing="0"><tr>
    <td><div class="card-inner"><span class="c-lbl">Total Collected</span><span class="c-val">&#8369;{{ number_format($data['total_collected'], 0) }}</span></div></td>
    <td><div class="card-inner amber"><span class="c-lbl">Pending</span><span class="c-val amber">&#8369;{{ number_format($data['total_pending'], 0) }}</span></div></td>
    <td><div class="card-inner red"><span class="c-lbl">Outstanding</span><span class="c-val red">&#8369;{{ number_format($data['outstanding_amt'], 0) }}</span></div></td>
    <td><div class="card-inner blue"><span class="c-lbl">Refunded</span><span class="c-val blue">&#8369;{{ number_format($data['total_refunded'], 0) }}</span></div></td>
</tr></table>

{{-- HIGHLIGHT BOXES --}}
<table class="hl-tbl" cellpadding="0" cellspacing="0"><tr>
    <td style="padding-left:0;">
        <div class="hl-inner" style="background:#059669;color:#fff;">
            <span class="hl-lbl">Total Collected This Period</span>
            <span class="hl-val">&#8369;{{ number_format($data['total_collected'], 2) }}</span>
            <span class="hl-sub">{{ $data['by_method']->sum('count') }} transaction(s) across all methods</span>
        </div>
    </td>
    <td style="padding-right:0;">
        <div class="hl-inner" style="background:#dc2626;color:#fff;">
            <span class="hl-lbl">Total Outstanding Balance</span>
            <span class="hl-val">&#8369;{{ number_format($data['outstanding_amt'], 2) }}</span>
            <span class="hl-sub">Amount still owed by parishioners</span>
        </div>
    </td>
</tr></table>

{{-- DEBIT / CREDIT BREAKDOWN --}}
<table style="width:100%;border-collapse:collapse;margin:0 0 9pt 0;" cellpadding="0" cellspacing="0"><tr>
    <td style="padding:0 3pt 0 0;">
        <div style="background:#fef2f2;border:0.75pt solid #fecaca;border-left:3pt solid #dc2626;padding:8pt 10pt;">
            <span style="display:block;font-size:5.5pt;text-transform:uppercase;font-weight:bold;color:#b91c1c;letter-spacing:0.8pt;margin-bottom:2pt;">Total Debit</span>
            <span style="display:block;font-size:15pt;font-weight:bold;color:#dc2626;line-height:1;">&#8369;{{ number_format($data['total_debit'] ?? 0, 2) }}</span>
            <span style="display:block;font-size:6.5pt;color:#9ca3af;margin-top:2pt;">{{ $data['debit_count'] ?? 0 }} debit transaction(s) &mdash; fees paid by parishioners</span>
        </div>
    </td>
    <td style="padding:0 0 0 3pt;">
        <div style="background:#f0fdf4;border:0.75pt solid #bbf7d0;border-left:3pt solid #16a34a;padding:8pt 10pt;">
            <span style="display:block;font-size:5.5pt;text-transform:uppercase;font-weight:bold;color:#15803d;letter-spacing:0.8pt;margin-bottom:2pt;">Total Credit</span>
            <span style="display:block;font-size:15pt;font-weight:bold;color:#16a34a;line-height:1;">&#8369;{{ number_format($data['total_credit'] ?? 0, 2) }}</span>
            <span style="display:block;font-size:6.5pt;color:#9ca3af;margin-top:2pt;">{{ $data['credit_count'] ?? 0 }} credit transaction(s) &mdash; refunds / adjustments</span>
        </div>
    </td>
</tr></table>

{{-- NET TOTAL ROW --}}
<div style="background:#1e3a8a;color:#fff;padding:5pt 10pt;margin-bottom:9pt;font-size:8pt;">
    <strong>NET TOTAL (Debit &minus; Credit):</strong>
    &nbsp;&nbsp;&#8369;{{ number_format(($data['total_debit'] ?? 0) - ($data['total_credit'] ?? 0), 2) }}
    &nbsp;&nbsp;&middot;&nbsp;&nbsp;
    {{ ($data['debit_count'] ?? 0) + ($data['credit_count'] ?? 0) }} total transaction(s)
</div>

{{-- BY METHOD --}}
<div class="sec-hdr">Collections by Payment Method</div>
<table class="dt" cellpadding="0" cellspacing="0">
    <thead><tr>
        <th>Payment Method</th>
        <th class="tr">Transactions</th>
        <th class="tr">Total (PHP)</th>
        <th class="tr">% Share</th>
    </tr></thead>
    <tbody>
        @php $cTotal = max($data['total_collected'], 1); @endphp
        @foreach($data['by_method'] as $m)
        <tr>
            <td>{{ \App\Models\Payment::METHODS[$m->payment_method] ?? ucfirst($m->payment_method) }}</td>
            <td class="tr">{{ number_format($m->count) }}</td>
            <td class="tr">&#8369;{{ number_format($m->total, 2) }}</td>
            <td class="tr">{{ round($m->total/$cTotal*100,1) }}%</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td>TOTAL COLLECTED</td>
            <td class="tr">{{ $data['by_method']->sum('count') }}</td>
            <td class="tr">&#8369;{{ number_format($data['total_collected'], 2) }}</td>
            <td class="tr">100%</td>
        </tr>
    </tbody>
</table>

{{-- DAILY COLLECTIONS --}}
@if($data['daily']->count())
<div class="sec-hdr">Daily Collections Summary</div>
<table class="dt" cellpadding="0" cellspacing="0">
    <thead><tr>
        <th>Date</th>
        <th class="tr">Amount (PHP)</th>
        <th class="tr">Transactions</th>
    </tr></thead>
    <tbody>
        @foreach($data['daily'] as $d)
        <tr>
            <td>{{ \Carbon\Carbon::parse($d->date)->format('M d, Y (l)') }}</td>
            <td class="tr">&#8369;{{ number_format($d->total, 2) }}</td>
            <td class="tr">{{ $d->count ?? 1 }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td>SUBTOTAL</td>
            <td class="tr">&#8369;{{ number_format($data['daily']->sum('total'), 2) }}</td>
            <td class="tr">{{ $data['daily']->sum('count') }}</td>
        </tr>
    </tbody>
</table>
@endif

<hr class="divider">

{{-- PERIOD / PRINTED — flows naturally at bottom of content --}}
<table style="width:100%;border-collapse:collapse;margin-top:10pt;padding-top:5pt;border-top:0.5pt solid #bbf7d0;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="font-size:7.5pt;color:#374151;">{{ $parish['name'] }} &middot; Payment Report &middot; Confidential</td>
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
    <td>{{ $parish['name'] }} &middot; Payment Report &middot; Confidential</td>
    <td style="text-align:right;">{{ $printedAt }}</td>
</tr></table>

</div>{{-- /.page-content --}}

{{-- COPYRIGHT — fixed at very bottom of every printed page --}}
<div class="page-copyright">
    &copy; {{ date('Y') }} {{ $parish['name'] }} &mdash; All rights reserved.
</div>

</body>
</html>
