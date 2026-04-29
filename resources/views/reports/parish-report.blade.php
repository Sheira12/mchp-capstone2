<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #1a1a1a; }
        .page { padding: 0.75in; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1a3a6b; padding-bottom: 15px; }
        .parish-name { font-size: 16pt; font-weight: bold; color: #1a3a6b; }
        .report-title { font-size: 14pt; font-weight: bold; margin: 10px 0 5px; }
        .period { font-size: 10pt; color: #555; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 12pt; font-weight: bold; color: #1a3a6b; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th { background: #1a3a6b; color: white; padding: 6px 10px; text-align: left; font-size: 10pt; }
        td { padding: 5px 10px; border-bottom: 1px solid #eee; font-size: 10pt; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .total-row td { font-weight: bold; background: #e8f0fe; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px; }
        .stat-box { border: 1px solid #ddd; border-radius: 5px; padding: 10px; text-align: center; }
        .stat-value { font-size: 18pt; font-weight: bold; color: #1a3a6b; }
        .stat-label { font-size: 9pt; color: #555; }
        .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 9pt; color: #888; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <p class="parish-name">{{ $parish['name'] }}</p>
        <p style="font-size:10pt; color:#555;">{{ $parish['address'] }}</p>
        <p class="report-title">Parish Administrative Report</p>
        <p class="period">Period: {{ \Carbon\Carbon::parse($from)->format('F d, Y') }} – {{ \Carbon\Carbon::parse($to)->format('F d, Y') }}</p>
        <p class="period">Generated: {{ now()->format('F d, Y g:i A') }}</p>
    </div>

    {{-- Summary Stats --}}
    <div class="section">
        <div class="section-title">Summary</div>
        <div class="stat-grid">
            <div class="stat-box">
                <div class="stat-value">{{ number_format($data['parishioners']['total']) }}</div>
                <div class="stat-label">Total Parishioners</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $data['parishioners']['new'] }}</div>
                <div class="stat-label">New This Period</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">₱{{ number_format($data['revenue']['total'], 0) }}</div>
                <div class="stat-label">Revenue Collected</div>
            </div>
        </div>
    </div>

    {{-- Sacraments --}}
    <div class="section">
        <div class="section-title">Sacraments Administered</div>
        <table>
            <thead><tr><th>Sacrament</th><th>Count</th></tr></thead>
            <tbody>
                @php $sacramentLabels = ['baptism' => 'Baptism', 'first_communion' => 'First Communion', 'confirmation' => 'Confirmation', 'marriage' => 'Marriage', 'death_burial' => 'Death/Burial']; @endphp
                @foreach($sacramentLabels as $key => $label)
                <tr><td>{{ $label }}</td><td>{{ $data['sacraments'][$key] ?? 0 }}</td></tr>
                @endforeach
                <tr class="total-row"><td>Total</td><td>{{ array_sum($data['sacraments']) }}</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Bookings --}}
    <div class="section">
        <div class="section-title">Bookings</div>
        <table>
            <thead><tr><th>Status</th><th>Count</th></tr></thead>
            <tbody>
                <tr><td>Pending</td><td>{{ $data['bookings']['pending'] }}</td></tr>
                <tr><td>Confirmed</td><td>{{ $data['bookings']['confirmed'] }}</td></tr>
                <tr><td>Completed</td><td>{{ $data['bookings']['completed'] }}</td></tr>
                <tr><td>Cancelled</td><td>{{ $data['bookings']['cancelled'] }}</td></tr>
                <tr class="total-row"><td>Total</td><td>{{ $data['bookings']['total'] }}</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Revenue --}}
    <div class="section">
        <div class="section-title">Revenue by Payment Method</div>
        <table>
            <thead><tr><th>Method</th><th>Amount</th></tr></thead>
            <tbody>
                @foreach($data['revenue']['by_method'] as $method => $amount)
                <tr><td>{{ ucfirst($method) }}</td><td>₱{{ number_format($amount, 2) }}</td></tr>
                @endforeach
                <tr class="total-row"><td>Total Revenue</td><td>₱{{ number_format($data['revenue']['total'], 2) }}</td></tr>
                @if($data['revenue']['refunded'] > 0)
                <tr><td>Refunded</td><td>₱{{ number_format($data['revenue']['refunded'], 2) }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>{{ $parish['name'] }} — Confidential Parish Report</p>
        <p>This report is for internal use only.</p>
    </div>
</div>
</body>
</html>
