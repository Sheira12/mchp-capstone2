<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
@page { size: A4 portrait; margin: 20pt 24pt 18pt 24pt; }
* { margin:0; padding:0; box-sizing:border-box; }
body { 
    font-family: DejaVu Sans, Arial, sans-serif; 
    font-size:9pt; 
    color:#1a1a2e; 
    background:#fff;
    border: 4pt solid #7c3aed;
    padding: 12pt 16pt;
    min-height: 100vh;
}

/* Inner decorative border */
body::before {
    content: '';
    position: absolute;
    top: 16pt;
    left: 20pt;
    right: 20pt;
    bottom: 16pt;
    border: 1pt solid #a78bfa;
    pointer-events: none;
}

.page-content { position:relative; z-index:1; }

.hdr { width:100%; border-collapse:collapse; margin-bottom:5pt; padding-bottom:5pt; border-bottom:2pt solid #7c3aed; }
.hdr td { vertical-align:middle; }
.hdr-logo { width:46pt; }
.hdr-logo img { width:42pt; height:42pt; border-radius:50%; border:2pt solid #d4af37; display:block; }
.hdr-center { text-align:center; }
.parish-name { font-size:11.5pt; font-weight:bold; color:#7c3aed; }
.parish-sub { font-size:6.5pt; color:#6b7280; margin-top:1pt; }
.rpt-title { font-size:10.5pt; font-weight:bold; letter-spacing:1.8pt; text-transform:uppercase; color:#1e293b; margin-top:3pt; }
.rpt-meta  { font-size:6.5pt; color:#6b7280; margin-top:1pt; }
.accent-bar { height:2pt; background:#7c3aed; margin-bottom:6pt; }

.cards { width:100%; border-collapse:collapse; margin-bottom:7pt; }
.cards td { padding:0 2.5pt; text-align:center; }
.card-inner { padding:7pt 5pt; border-radius:3pt; }
.card-lbl { font-size:5.5pt; text-transform:uppercase; font-weight:bold; letter-spacing:0.7pt; display:block; margin-bottom:1.5pt; }
.card-val { font-size:15pt; font-weight:bold; line-height:1; display:block; }
.card-sub { font-size:5.5pt; display:block; margin-top:1.5pt; }

.sec-hdr { padding:3.5pt 6pt; font-size:8pt; font-weight:bold; color:#fff; margin-bottom:0; margin-top:6pt; }

table.dt { width:100%; border-collapse:collapse; font-size:7.5pt; }
table.dt thead tr { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
table.dt th { color:#fff; padding:3.5pt 4.5pt; font-weight:bold; font-size:7pt; }
table.dt td { padding:3pt 4.5pt; border-bottom:0.4pt solid #f1f5f9; }
table.dt tbody tr.even td { background:#f8f5ff; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
table.dt tfoot td { font-weight:bold; padding:3.5pt 4.5pt; }
table.dt tfoot tr.subtotal td { background:#ede9fe; border-top:1pt solid #7c3aed; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
table.dt tfoot tr.total td { background:#7c3aed; color:#fff; border-top:1.5pt solid #5b21b6; font-size:8.5pt; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
.tr { text-align:right; }
.tc { text-align:center; }
.green { color:#065f46; }
.red   { color:#991b1b; }
.blue  { color:#1e40af; }

.divider { border:none; border-top:0.5pt solid #ddd6fe; margin:5pt 0; }

.sig-wrap { width:100%; border-collapse:collapse; margin-top:8pt; page-break-inside:avoid; }
.sig-wrap td { width:33.33%; text-align:center; padding:0 6pt; }
.sig-line { border-top:1pt solid #374151; margin:0 auto; width:86%; padding-top:2.5pt; font-size:7.5pt; font-weight:bold; }
.sig-role { font-size:6.5pt; color:#6b7280; margin-top:0.8pt; }

.footer { margin-top:6pt; padding-top:3pt; border-top:0.5pt solid #ddd6fe; width:100%; border-collapse:collapse; page-break-inside:avoid; }
.footer td { font-size:6pt; color:#9ca3af; }
</style>
</head>
<body>

<div class="page-content">

{{-- HEADER --}}
<table class="hdr" cellpadding="0" cellspacing="0"><tr>
    <td class="hdr-logo">@if(file_exists($logoPath))<img src="{{ $logoPath }}" alt="Logo">@endif</td>
    <td class="hdr-center">
        <div class="parish-name">{{ $parish['name'] }}</div>
        <div class="parish-sub">{{ $parish['address'] }} &nbsp;&middot;&nbsp; {{ $parish['phone'] }}</div>
        <div class="rpt-title">Financial Report &mdash; Credit &amp; Debit Statement</div>
        <div class="rpt-meta">Period: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($to)->format('M d, Y') }} &nbsp;|&nbsp; Printed: {{ $printedAt }}</div>
    </td>
    <td style="width:46pt;"></td>
</tr></table>

<div class="accent-bar"></div>

{{-- SUMMARY CARDS --}}
<table class="cards" cellpadding="0" cellspacing="0"><tr>
    <td style="padding-left:0;">
        <div class="card-inner" style="background:#f0fdf4;border:0.75pt solid #bbf7d0;">
            <span class="card-lbl" style="color:#065f46;">Total Income</span>
            <span class="card-val green">&#8369;{{ number_format($totalCredit,2) }}</span>
            <span class="card-sub" style="color:#16a34a;">{{ $entries->where('type','credit')->count() }} transaction(s)</span>
        </div>
    </td>
    <td>
        <div class="card-inner" style="background:#fef2f2;border:0.75pt solid #fecaca;">
            <span class="card-lbl" style="color:#991b1b;">Total Expenses</span>
            <span class="card-val red">&#8369;{{ number_format($totalDebit,2) }}</span>
            <span class="card-sub" style="color:#dc2626;">{{ $entries->where('type','debit')->count() }} transaction(s)</span>
        </div>
    </td>
    <td style="padding-right:0;">
        <div class="card-inner" style="background:{{ $netBalance>=0 ? '#eff6ff' : '#fef2f2' }};border:0.75pt solid {{ $netBalance>=0 ? '#bfdbfe' : '#fecaca' }};">
            <span class="card-lbl" style="color:{{ $netBalance>=0 ? '#1e40af' : '#991b1b' }};">Net Balance</span>
            <span class="card-val" style="color:{{ $netBalance>=0 ? '#1d4ed8' : '#dc2626' }};">{{ $netBalance<0 ? '-' : '' }}&#8369;{{ number_format(abs($netBalance),2) }}</span>
            <span class="card-sub" style="color:{{ $netBalance>=0 ? '#3b82f6' : '#ef4444' }};">{{ $netBalance>=0 ? 'Surplus' : 'Deficit' }}</span>
        </div>
    </td>
</tr></table>

{{-- CATEGORY SUMMARY --}}
@if($byCategory->count())
<div class="sec-hdr" style="background:#7c3aed;">Summary by Category</div>
<table class="dt" cellpadding="0" cellspacing="0">
    <thead><tr style="background:#6d28d9;">
        <th>Category</th><th>Type</th><th class="tr">Entries</th><th class="tr">Total Amount</th>
    </tr></thead>
    <tbody>
        @foreach($byCategory as $cat => $info)
        <tr class="{{ $loop->even ? 'even' : '' }}">
            <td style="font-weight:500;">{{ $cat }}</td>
            <td style="color:{{ $info['type']==='credit' ? '#065f46' : '#991b1b' }};font-weight:bold;font-size:7pt;white-space:nowrap;">
                {!! $info['type']==='credit' ? '&#x2191; Income' : '&#x2193; Expense' !!}
            </td>
            <td class="tr">{{ $info['count'] }}</td>
            <td class="tr {{ $info['type']==='credit' ? 'green' : 'red' }}" style="font-weight:bold;">&#8369;{{ number_format($info['total'],2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- DETAILED TRANSACTIONS --}}
@if($entries->count())
<div class="sec-hdr" style="background:#374151;">Detailed Transactions</div>
<table class="dt" cellpadding="0" cellspacing="0">
    <thead><tr style="background:#1f2937;">
        <th style="width:48pt;">Date</th>
        <th style="width:40pt;">Type</th>
        <th style="width:62pt;">Category</th>
        <th>Description</th>
        <th class="tr" style="width:58pt;">Amount</th>
    </tr></thead>
    <tbody>
        @foreach($entries as $i => $entry)
        <tr class="{{ $i%2===0 ? 'even' : '' }}">
            <td style="font-size:7pt;color:#6b7280;white-space:nowrap;">{{ $entry->entry_date->format('M d, Y') }}</td>
            <td style="font-size:7pt;font-weight:bold;color:{{ $entry->type==='credit' ? '#065f46' : '#991b1b' }};white-space:nowrap;">
                {!! $entry->type==='credit' ? '&#x2191; Income' : '&#x2193; Expense' !!}
            </td>
            <td style="font-size:7pt;color:#6b7280;">{{ $entry->category }}</td>
            <td>{{ $entry->description }}</td>
            <td class="tr {{ $entry->type==='credit' ? 'green' : 'red' }}" style="font-weight:bold;white-space:nowrap;">
                {{ $entry->type==='credit' ? '+' : '-' }}&#8369;{{ number_format($entry->amount,2) }}
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="subtotal"><td colspan="3"></td><td style="text-align:right;font-size:7.5pt;">Total Income:</td><td class="tr green">+&#8369;{{ number_format($totalCredit,2) }}</td></tr>
        <tr class="subtotal"><td colspan="3"></td><td style="text-align:right;font-size:7.5pt;">Total Expenses:</td><td class="tr red">-&#8369;{{ number_format($totalDebit,2) }}</td></tr>
        <tr class="total">
            <td colspan="3"></td>
            <td style="text-align:right;font-size:8.5pt;">NET BALANCE:</td>
            <td class="tr" style="font-size:8.5pt;">{{ $netBalance<0?'-':'+' }}&#8369;{{ number_format(abs($netBalance),2) }}</td>
        </tr>
    </tfoot>
</table>
@endif

<hr class="divider">

{{-- SIGNATURES --}}
<table class="sig-wrap" cellpadding="0" cellspacing="0"><tr>
    <td><div class="sig-line">Prepared by</div><div class="sig-role">Parish Secretary</div></td>
    <td><div class="sig-line">Verified by</div><div class="sig-role">Finance Officer</div></td>
    <td><div class="sig-line">{{ $parish['priest'] }}</div><div class="sig-role">Parish Priest</div></td>
</tr></table>

{{-- FOOTER --}}
<table class="footer" cellpadding="0" cellspacing="0"><tr>
    <td>{{ $parish['name'] }} &middot; Financial Report &middot; Confidential</td>
    <td style="text-align:right;">{{ $printedAt }}</td>
</tr></table>

</div>
</body>
</html>
