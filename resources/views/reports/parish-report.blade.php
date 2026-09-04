<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        /* ═══════════════════════════════════════════════
           MARY HELP OF CHRISTIANS PARISH — ADMIN REPORT
           Engine: DomPDF  |  Paper: Letter Portrait
           Navy #1F3A5F · Gold #D4AF37
           ═══════════════════════════════════════════════ */

        @page { size: A4 portrait; margin: 15mm 16mm 22mm 16mm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #1a1a2e;
            background: #fff;
        }

        /* ── Header ── */
        .report-header {
            text-align: center;
            padding-bottom: 14pt;
            margin-bottom: 14pt;
            border-bottom: 3pt solid #1F3A5F;
            position: relative;
        }
        .report-header::after {
            content: '';
            display: block;
            height: 1pt;
            background: #D4AF37;
            margin-top: 2pt;
        }
        .header-inner {
            display: table; width: 100%;
        }
        .header-logo { display: table-cell; vertical-align: middle; width: 60pt; text-align: left; }
        .header-logo img { width: 52pt; height: 52pt; border-radius: 50%; border: 2pt solid #D4AF37; }
        .header-text { display: table-cell; vertical-align: middle; text-align: center; }
        .header-right { display: table-cell; vertical-align: middle; width: 60pt; text-align: right; }

        .parish-title {
            font-size: 16pt; font-weight: bold;
            color: #1F3A5F; letter-spacing: 0.5pt;
            margin-bottom: 2pt;
        }
        .diocese-label {
            font-size: 7.5pt; color: #D4AF37;
            font-weight: bold; letter-spacing: 1.5pt;
            text-transform: uppercase; margin-bottom: 4pt;
        }
        .report-title {
            font-size: 13pt; font-weight: bold;
            color: #1F3A5F; margin-bottom: 2pt;
        }
        .report-period {
            font-size: 9pt; color: #64748b;
            margin-bottom: 2pt;
        }
        .report-generated {
            font-size: 7.5pt; color: #94a3b8;
        }

        /* ── Section headings ── */
        .section { margin-bottom: 16pt; }
        .section-title {
            font-size: 11pt; font-weight: bold;
            color: #fff; background: #1F3A5F;
            padding: 5pt 10pt;
            margin-bottom: 0;
            letter-spacing: 0.5pt;
        }
        .section-title-accent {
            display: inline-block;
            width: 4pt; height: 100%;
            background: #D4AF37;
            margin-right: 6pt;
        }

        /* ── Summary stat boxes ── */
        .stat-grid {
            display: table; width: 100%;
            border-collapse: separate; border-spacing: 6pt;
            margin-bottom: 6pt;
        }
        .stat-cell { display: table-cell; width: 33.33%; }
        .stat-box {
            border: 1pt solid #e2e8f0;
            border-top: 3pt solid #1F3A5F;
            border-radius: 4pt;
            padding: 8pt 10pt;
            text-align: center;
            background: #f8faff;
        }
        .stat-box.gold { border-top-color: #D4AF37; }
        .stat-box.green { border-top-color: #16a34a; }
        .stat-value {
            font-size: 22pt; font-weight: 900;
            color: #1F3A5F; line-height: 1; margin-bottom: 3pt;
        }
        .stat-label {
            font-size: 7.5pt; font-weight: bold;
            color: #64748b; text-transform: uppercase;
            letter-spacing: 0.5pt;
        }

        /* ── Tables ── */
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: #1F3A5F; }
        thead th {
            color: #fff; font-size: 8pt; font-weight: bold;
            padding: 5pt 8pt; text-align: left;
            letter-spacing: 0.3pt; text-transform: uppercase;
        }
        tbody tr { border-bottom: 0.5pt solid #e2e8f0; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:nth-child(even) td { background: #f8faff; }
        tbody td {
            padding: 5pt 8pt; font-size: 9.5pt;
            color: #1a1a2e;
        }
        .total-row td {
            background: #1F3A5F !important; color: #fff !important;
            font-weight: bold; font-size: 10pt;
        }
        td.amount { text-align: right; font-weight: bold; }
        td.center { text-align: center; }

        /* ── Revenue highlight ── */
        .revenue-total {
            background: linear-gradient(135deg, #1F3A5F, #2d5282);
            color: #fff; border-radius: 6pt;
            padding: 10pt 14pt; margin-top: 8pt;
            display: table; width: 100%;
        }
        .revenue-total-left  { display: table-cell; vertical-align: middle; font-size: 10pt; font-weight: bold; }
        .revenue-total-right { display: table-cell; vertical-align: middle; text-align: right; font-size: 18pt; font-weight: 900; color: #D4AF37; }

        /* ── Footer ── */
        .report-footer {
            border-top: 1pt solid #D4AF37;
            padding-top: 4pt;
            margin-top: 14pt;
            display: table; width: 100%;
            font-size: 7pt; color: #94a3b8;
        }
        .footer-left  { display: table-cell; vertical-align: middle; width: 50%; }
        .footer-right { display: table-cell; vertical-align: middle; text-align: right; width: 50%; }

        /* ── Copyright — fixed at bottom of every page ── */
        .page-copyright {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            text-align: center;
            font-size: 6.5pt;
            color: #94a3b8;
            border-top: 0.5pt solid #D4AF37;
            padding: 3pt 15mm;
            background: #fff;
        }

        /* ── Page break rules ── */
        .section { page-break-inside: avoid; break-inside: avoid; margin-bottom: 16pt; }
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; break-inside: avoid; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }

        /* ── Divider ── */
        .gold-bar {
            height: 2pt; background: #D4AF37;
            margin: 10pt 0; border-radius: 9999pt;
        }
        .separator {
            border: none; border-top: 0.5pt solid #e2e8f0; margin: 8pt 0;
        }
    </style>
</head>
<body>

{{-- ── HEADER ── --}}
<div class="report-header">
    <div class="header-inner">
        <div class="header-logo">
            @if(file_exists(public_path('images/parish-logo.png')))
            <img src="{{ public_path('images/parish-logo.png') }}" alt="Parish Logo">
            @endif
        </div>
        <div class="header-text">
            <div class="diocese-label">Diocese of San Pablo · Archdiocese of Lipa</div>
            <div class="parish-title">{{ $parish['name'] }}</div>
            <div style="height:1pt;background:#D4AF37;width:80pt;margin:4pt auto;border-radius:9999pt;"></div>
            <div class="report-title">Parish Administrative Report</div>
            <div class="report-period">
                Period: {{ \Carbon\Carbon::parse($from)->format('F d, Y') }}
                — {{ \Carbon\Carbon::parse($to)->format('F d, Y') }}
            </div>
        </div>
        <div class="header-right"></div>
    </div>
</div>

{{-- ── SUMMARY ── --}}
<div class="section">
    <div class="section-title">Executive Summary</div>
    <div style="height:1pt;background:#D4AF37;margin-bottom:8pt;"></div>

    <div class="stat-grid">
        <div class="stat-cell">
            <div class="stat-box">
                <div class="stat-value">{{ number_format($data['parishioners']['total']) }}</div>
                <div class="stat-label">Total Parishioners</div>
            </div>
        </div>
        <div class="stat-cell">
            <div class="stat-box gold">
                <div class="stat-value">{{ $data['parishioners']['new'] }}</div>
                <div class="stat-label">New This Period</div>
            </div>
        </div>
        <div class="stat-cell">
            <div class="stat-box green">
                <div class="stat-value">{{ $data['bookings']['total'] }}</div>
                <div class="stat-label">Total Bookings</div>
            </div>
        </div>
    </div>

    <div class="revenue-total">
        <div class="revenue-total-left">Total Revenue Collected</div>
        <div class="revenue-total-right">&#8369;{{ number_format($data['revenue']['total'], 2) }}</div>
    </div>
</div>

{{-- ── SACRAMENTS ── --}}
<div class="section">
    <div class="section-title">Sacraments Administered</div>
    <div style="height:1pt;background:#D4AF37;margin-bottom:0;"></div>

    @php
    $sacramentLabels = [
        'baptism'         => 'Baptism',
        'first_communion' => 'First Holy Communion',
        'confirmation'    => 'Confirmation',
        'marriage'        => 'Marriage',
        'death_burial'    => 'Death & Burial',
    ];
    $totalSacraments = array_sum($data['sacraments']);
    @endphp

    <table>
        <thead>
            <tr>
                <th>Sacrament</th>
                <th class="center">Count</th>
                <th class="center">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sacramentLabels as $key => $label)
            @php $count = $data['sacraments'][$key] ?? 0; $pct = $totalSacraments > 0 ? round(($count / $totalSacraments) * 100, 1) : 0; @endphp
            <tr>
                <td>{{ $label }}</td>
                <td class="center" style="font-weight:bold;">{{ $count }}</td>
                <td class="center" style="color:#64748b;">{{ $pct }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="center">{{ $totalSacraments }}</td>
                <td class="center">100%</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── BOOKINGS ── --}}
<div class="section">
    <div class="section-title">Bookings Summary</div>
    <div style="height:1pt;background:#D4AF37;margin-bottom:0;"></div>

    @php
    $totalBookings = $data['bookings']['total'] ?: 1;
    @endphp

    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="center">Count</th>
                <th class="center">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach(['pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'] as $key => $label)
            @php $count = $data['bookings'][$key] ?? 0; $pct = round(($count / $totalBookings) * 100, 1); @endphp
            <tr>
                <td>{{ $label }}</td>
                <td class="center" style="font-weight:bold;">{{ $count }}</td>
                <td class="center" style="color:#64748b;">{{ $pct }}%</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>TOTAL</td>
                <td class="center">{{ $data['bookings']['total'] }}</td>
                <td class="center">100%</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── REVENUE ── --}}
<div class="section">
    <div class="section-title">Revenue by Payment Method</div>
    <div style="height:1pt;background:#D4AF37;margin-bottom:0;"></div>

    @php $totalRev = $data['revenue']['total'] ?: 1; @endphp

    <table>
        <thead>
            <tr>
                <th>Payment Method</th>
                <th class="amount">Amount</th>
                <th class="center">% of Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['revenue']['by_method'] as $method => $amount)
            @php $pct = round(($amount / $totalRev) * 100, 1); @endphp
            <tr>
                <td style="text-transform:capitalize;">{{ $method }}</td>
                <td class="amount">&#8369;{{ number_format($amount, 2) }}</td>
                <td class="center" style="color:#64748b;">{{ $pct }}%</td>
            </tr>
            @endforeach
            @if($data['revenue']['refunded'] > 0)
            <tr>
                <td style="color:#dc2626;">Refunded</td>
                <td class="amount" style="color:#dc2626;">— &#8369;{{ number_format($data['revenue']['refunded'], 2) }}</td>
                <td class="center" style="color:#dc2626;">—</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>NET TOTAL REVENUE</td>
                <td class="amount">&#8369;{{ number_format($data['revenue']['total'] - $data['revenue']['refunded'], 2) }}</td>
                <td class="center">100%</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ── NOTES ── --}}
<div style="background:#f8faff;border:1pt solid #bfdbfe;border-radius:4pt;padding:8pt 10pt;margin-top:4pt;">
    <p style="font-size:8.5pt;color:#1e40af;font-weight:bold;margin-bottom:3pt;">Report Notes</p>
    <p style="font-size:8pt;color:#374151;line-height:1.6;">
        This report covers the period from
        <strong>{{ \Carbon\Carbon::parse($from)->format('F d, Y') }}</strong> to
        <strong>{{ \Carbon\Carbon::parse($to)->format('F d, Y') }}</strong>.
        All figures reflect data recorded in the parish management system.
        This document is confidential and for internal administrative use only.
    </p>
    <div style="margin-top:8pt;display:table;width:100%;">
        <div style="display:table-cell;vertical-align:bottom;width:40%;">
            <div style="border-top:1pt solid #1F3A5F;padding-top:3pt;margin-top:20pt;">
                <p style="font-size:9pt;font-weight:bold;color:#1F3A5F;">Parish Secretary</p>
                <p style="font-size:7.5pt;color:#64748b;text-transform:uppercase;letter-spacing:0.5pt;">Authorized Signatory</p>
            </div>
        </div>
        <div style="display:table-cell;width:20%;"></div>
        <div style="display:table-cell;vertical-align:bottom;width:40%;text-align:right;">
            <div style="border-top:1pt solid #1F3A5F;padding-top:3pt;margin-top:20pt;">
                <p style="font-size:9pt;font-weight:bold;color:#1F3A5F;">{{ $parish['priest'] ?? 'Parish Priest' }}</p>
                <p style="font-size:7.5pt;color:#64748b;text-transform:uppercase;letter-spacing:0.5pt;">Parish Priest</p>
            </div>
        </div>
    </div>
</div>

{{-- ── FOOTER — period/printed bottom-right, copyright fixed ── --}}
<div class="report-footer">
    <div class="footer-left">{{ $parish['name'] }} &middot; Parish Administrative Report &middot; CONFIDENTIAL</div>
    <div class="footer-right">
        Period: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}
        &nbsp;|&nbsp; Generated: {{ now()->format('M d, Y g:i A') }}
    </div>
</div>

{{-- COPYRIGHT — fixed at very bottom of every printed page --}}
<div class="page-copyright">
    &copy; {{ date('Y') }} {{ $parish['name'] }} &mdash; All rights reserved.
</div>

</body>
</html>
