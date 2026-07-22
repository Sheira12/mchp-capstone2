<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
@page { size: A4 portrait; margin: 0; }
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; color: #1a1a2e; line-height:1.4; background:#fff; }

/* ══════════════════════════════════════
   PAGE BORDER — navy/gold theme
   matches certificate premium style
   ══════════════════════════════════════ */
.border-outer {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    border: 5pt solid #1d4ed8;
    background: transparent;
    z-index: 999;
}
.border-gold {
    position: fixed;
    top: 7pt; left: 7pt; right: 7pt; bottom: 7pt;
    border: 1.2pt solid #d4af37;
    background: transparent;
    z-index: 998;
}
.border-thin {
    position: fixed;
    top: 10pt; left: 10pt; right: 10pt; bottom: 10pt;
    border: 0.5pt solid rgba(29,78,216,0.25);
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
.doc-title { font-size:11pt; font-weight:bold; letter-spacing:2.5pt; text-transform:uppercase; color:#1e293b; margin-top:5pt; }
.doc-meta  { font-size:7pt; color:#6b7280; margin-top:1pt; }

/* ── ACCENT BAR ── */
.accent-bar { height:2.5pt; background:linear-gradient(to right,#1d4ed8,#d4af37,#1d4ed8); margin-bottom:8pt; }

/* ── PARISHIONER INFO BOX ── */
.info-wrap { background:#f8fafc; border:0.75pt solid #e2e8f0; border-left:3.5pt solid #1d4ed8; padding:8pt 10pt; margin-bottom:8pt; }
.info-tbl { width:100%; border-collapse:collapse; }
.info-tbl td { font-size:8.5pt; padding:2pt 4pt; vertical-align:top; }
.info-key { width:90pt; font-weight:bold; color:#374151; }
.info-val { color:#1e293b; }

/* ── SUMMARY CARDS ── */
.sum-tbl { width:100%; border-collapse:collapse; margin-bottom:8pt; }
.sum-tbl td { padding:0 3pt; text-align:center; }
.sum-inner { padding:8pt 6pt; }
.sum-lbl { font-size:6pt; text-transform:uppercase; font-weight:bold; letter-spacing:0.8pt; display:block; margin-bottom:3pt; }
.sum-val { font-size:16pt; font-weight:bold; line-height:1; display:block; }
.sum-sub { font-size:6.5pt; display:block; margin-top:2pt; opacity:0.85; }

/* ── TRANSACTION TABLE ── */
table.txn { width:100%; border-collapse:collapse; font-size:7.8pt; }
table.txn thead tr { background:#1e3a8a; }
table.txn th { color:#fff; padding:4pt; font-weight:bold; font-size:7.5pt; }
table.txn td { padding:3.5pt 4pt; border-bottom:0.4pt solid #e8edf5; vertical-align:middle; }
table.txn tbody tr:nth-child(even) td { background:#f0f5ff; }
table.txn tfoot td { font-weight:bold; background:#dbeafe; border-top:1.5pt solid #1d4ed8; border-bottom:1.5pt solid #1d4ed8; padding:4pt; }
.tr { text-align:right; }
.tc { text-align:center; }
.mono { font-family:'Courier New',monospace; font-size:7pt; }

/* ── STATUS BADGES ── */
.badge { padding:1pt 4pt; font-size:6.5pt; font-weight:bold; }
.badge-paid    { background:#d1fae5; color:#065f46; }
.badge-pending { background:#fef3c7; color:#92400e; }
.badge-partial { background:#dbeafe; color:#1e40af; }
.badge-failed  { background:#fee2e2; color:#991b1b; }
.badge-refunded{ background:#ede9fe; color:#5b21b6; }

/* ── DIVIDER ── */
.divider { border:none; border-top:0.5pt solid #d4af37; margin:8pt 0; }

/* ── SIGNATURES ── */
.sig-wrap { width:100%; border-collapse:collapse; margin-top:14pt; }
.sig-wrap td { width:33.33%; text-align:center; padding:0 8pt; vertical-align:bottom; }
.sig-line { border-top:1pt solid #374151; margin:0 auto; width:86%; padding-top:4pt; font-size:8.5pt; font-weight:bold; color:#1e293b; }
.sig-role { font-size:7pt; color:#6b7280; margin-top:1.5pt; }

/* ── FOOTER ── */
.doc-footer { margin-top:9pt; padding-top:4pt; border-top:0.5pt solid #d4af37; text-align:center; font-size:6.5pt; color:#9ca3af; }
</style>
</head>
<body>

{{-- PAGE BORDER OVERLAYS --}}
<div class="border-outer"></div>
<div class="border-gold"></div>
<div class="border-thin"></div>

<div class="page-content">

{{-- HEADER --}}
<table class="hdr" cellpadding="0" cellspacing="0"><tr>
    <td class="hdr-logo">@if(file_exists($logoPath))<img src="{{ $logoPath }}" alt="Logo">@endif</td>
    <td class="hdr-center">
        <div class="parish-name">{{ $parish['name'] }}</div>
        <div class="parish-sub">{{ $parish['address'] }}<br>Tel: {{ $parish['phone'] }} &nbsp;&middot;&nbsp; {{ $parish['email'] }}</div>
        <div class="doc-title">Statement of Account</div>
        <div class="doc-meta">Printed: {{ $printedAt }}</div>
    </td>
    <td class="hdr-right"></td>
</tr></table>

<div class="accent-bar"></div>

{{-- PARISHIONER INFO --}}
<div class="info-wrap">
    <table class="info-tbl" cellpadding="0" cellspacing="0">
        <tr>
            <td class="info-key">Parishioner Name:</td>
            <td class="info-val"><strong>{{ $parishioner->full_name }}</strong></td>
            <td class="info-key">Parishioner ID:</td>
            <td class="info-val">#{{ $parishioner->id }}</td>
        </tr>
        <tr>
            <td class="info-key">Address:</td>
            <td class="info-val">
                @php
                    $addrParts = array_filter([
                        $parishioner->address ?? null,
                        $parishioner->barangay ? 'Brgy. ' . $parishioner->barangay : null,
                        $parishioner->city ?? 'Cabuyao',
                        'Laguna',
                    ]);
                    echo implode(', ', $addrParts) ?: '&mdash;';
                @endphp
            </td>
            <td class="info-key">Contact:</td>
            <td class="info-val">{{ $parishioner->contact_number ?? '—' }}</td>
        </tr>
        <tr>
            <td class="info-key">Date of Birth:</td>
            <td class="info-val">{{ $parishioner->birthdate?->format('F d, Y') ?? '&mdash;' }}</td>
            <td class="info-key">Email:</td>
            <td class="info-val">{{ $parishioner->email ?? '&mdash;' }}</td>
        </tr>
    </table>
</div>

{{-- SUMMARY CARDS --}}
<table class="sum-tbl" cellpadding="0" cellspacing="0"><tr>
    <td style="padding-left:0;">
        <div class="sum-inner" style="background:#1e293b;color:#fff;">
            <span class="sum-lbl" style="color:#94a3b8;">Total Amount Due</span>
            <span class="sum-val">&#8369;{{ number_format($totalDue, 2) }}</span>
            <span class="sum-sub">Total service fees incurred</span>
        </div>
    </td>
    <td>
        <div class="sum-inner" style="background:#059669;color:#fff;">
            <span class="sum-lbl" style="color:#a7f3d0;">Total Amount Paid</span>
            <span class="sum-val">&#8369;{{ number_format($totalPaid, 2) }}</span>
            <span class="sum-sub">Confirmed payments received</span>
        </div>
    </td>
    <td style="padding-right:0;">
        <div class="sum-inner" style="background:{{ $outstanding > 0 ? '#dc2626' : '#16a34a' }};color:#fff;">
            <span class="sum-lbl" style="color:{{ $outstanding > 0 ? '#fecaca' : '#bbf7d0' }};">Outstanding Balance</span>
            <span class="sum-val">&#8369;{{ number_format($outstanding, 2) }}</span>
            <span class="sum-sub">{{ $outstanding > 0 ? 'Balance still owed' : 'Fully settled' }}</span>
        </div>
    </td>
</tr></table>

{{-- TRANSACTION TABLE --}}
<table class="txn" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th style="width:52pt;">Date</th>
            <th style="width:78pt;">Reference</th>
            <th>Description</th>
            <th style="width:52pt;">Method</th>
            <th class="tr" style="width:44pt;">Amount Due</th>
            <th class="tr" style="width:44pt;">Amount Paid</th>
            <th class="tr" style="width:38pt;">Balance</th>
            <th class="tc" style="width:36pt;">Status</th>
        </tr>
    </thead>
    <tbody>
        @php $runningBal = 0; @endphp
        @forelse($payments as $payment)
        @php
            $due  = $payment->booking?->service_fee ?? $payment->amount ?? 0;
            $paid = $payment->status === 'paid' ? $payment->amount : 0;
            $runningBal += ($due - $paid);
            $badgeClass = match($payment->status) {
                'paid'     => 'badge-paid',
                'pending'  => 'badge-pending',
                'partial'  => 'badge-partial',
                'refunded' => 'badge-refunded',
                default    => 'badge-failed',
            };
        @endphp
        <tr>
            <td>{{ $payment->created_at->format('M d, Y') }}</td>
            <td class="mono">{{ $payment->reference_number ?? '—' }}</td>
            <td>
                @php
                    if ($payment->booking) {
                        echo $payment->booking->getTypeLabel();
                    } elseif (!empty($payment->certificate)) {
                        echo $payment->certificate->getTypeLabel();
                    } else {
                        echo $payment->notes ?? 'Parish Service';
                    }
                @endphp
            </td>
            <td>{{ \App\Models\Payment::METHODS[$payment->payment_method] ?? ucfirst($payment->payment_method) }}</td>
            <td class="tr">&#8369;{{ number_format($due, 2) }}</td>
            <td class="tr">{{ $paid > 0 ? '&#8369;' . number_format($paid, 2) : '—' }}</td>
            <td class="tr">&#8369;{{ number_format(max(0,$runningBal), 2) }}</td>
            <td class="tc"><span class="badge {{ $badgeClass }}">{{ strtoupper($payment->status) }}</span></td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="tc" style="padding:14pt;color:#9ca3af;font-style:italic;">No transactions recorded for this parishioner.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="tr" style="font-size:8.5pt;padding-right:6pt;">TOTALS:</td>
            <td class="tr">&#8369;{{ number_format($totalDue, 2) }}</td>
            <td class="tr">&#8369;{{ number_format($totalPaid, 2) }}</td>
            <td class="tr" style="color:{{ $outstanding > 0 ? '#dc2626' : '#16a34a' }};">&#8369;{{ number_format($outstanding, 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<hr class="divider">

{{-- SIGNATURES --}}
<table class="sig-wrap" cellpadding="0" cellspacing="0"><tr>
    <td><div class="sig-line">Prepared by</div><div class="sig-role">Parish Secretary</div></td>
    <td><div class="sig-line">Verified by</div><div class="sig-role">Finance Officer</div></td>
    <td><div class="sig-line">{{ $parish['priest'] ?? config('parish.priest') }}</div><div class="sig-role">Parish Priest</div></td>
</tr></table>

{{-- FOOTER --}}
<div class="doc-footer">
    This is an official document of {{ $parish['name'] }}. &nbsp;&middot;&nbsp; Generated on {{ $printedAt }}<br>
    For inquiries, contact the parish office at {{ $parish['phone'] }} or {{ $parish['email'] }}
</div>

</div>{{-- /.page-content --}}
</body>
</html>
