<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Order of Payment — {{ $order->order_number }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
@page { margin:18mm 16mm; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10pt; color:#1e293b; }

.border-frame { border:2.5pt solid #1e3a8a; padding:0; }

.header { background:#1e3a8a; color:#fff; text-align:center; padding:14px 20px 10px; }
.header-parish { font-size:7pt; letter-spacing:3px; text-transform:uppercase; color:#bfdbfe; margin-bottom:4px; }
.header-title { font-size:16pt; font-weight:bold; margin-bottom:2px; }
.header-sub { font-size:8pt; color:#bfdbfe; }

.body { padding:16px 20px; }

.section-title { font-size:7pt; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#64748b; border-bottom:1pt solid #e2e8f0; padding-bottom:3px; margin-bottom:8px; margin-top:14px; }

.info-table { width:100%; border-collapse:collapse; margin-bottom:8px; }
.info-table td { padding:3px 0; font-size:9pt; vertical-align:top; }
.info-table .label { color:#64748b; width:35%; font-size:8pt; }
.info-table .value { font-weight:bold; color:#0f172a; }

.line-items { width:100%; border-collapse:collapse; margin-top:8px; }
.line-items th { background:#f1f5f9; font-size:8pt; font-weight:bold; text-align:left; padding:6px 10px; border:0.5pt solid #e2e8f0; color:#475569; }
.line-items td { padding:5px 10px; border:0.5pt solid #e2e8f0; font-size:9pt; vertical-align:top; }
.line-items td.amount { text-align:right; white-space:nowrap; }
.total-row { background:#eff6ff; }
.total-row td { font-weight:bold; font-size:11pt; color:#1e3a8a; padding:8px 10px; border:1pt solid #bfdbfe; }
.total-row td.amount { text-align:right; }

.status-badge { display:inline-block; padding:3px 12px; border-radius:20px; font-size:8pt; font-weight:bold; }
.status-pending { background:#fef9c3; color:#854d0e; border:0.5pt solid #fde047; }
.status-paid { background:#dcfce7; color:#166534; border:0.5pt solid #86efac; }

.note-box { background:#f8fafc; border:0.5pt solid #e2e8f0; padding:6px 10px; margin-top:10px; font-size:8pt; color:#475569; border-radius:4px; }

.footer { margin-top:18px; padding-top:10px; border-top:0.5pt solid #e2e8f0; font-size:7.5pt; color:#94a3b8; text-align:center; }
</style>
</head>
<body>

<div class="border-frame">
    <div class="header">
        <p class="header-parish">{{ $parish['name'] }}</p>
        <p class="header-title">ORDER OF PAYMENT</p>
        <p class="header-sub">{{ $order->order_number }} &nbsp;|&nbsp; {{ $order->created_at->format('F d, Y') }}</p>
    </div>

    <div class="body">
        {{-- Status --}}
        <div style="margin-bottom:10px;">
            @if($order->status === 'paid')
            <span class="status-badge status-paid">&#10003; PAID</span>
            @else
            <span class="status-badge status-pending">PENDING PAYMENT</span>
            @endif
        </div>

        {{-- Client info --}}
        <p class="section-title">Parishioner / Client Information</p>
        <table class="info-table">
            <tr>
                <td class="label">Name:</td>
                <td class="value">{{ $order->parishioner->full_name }}</td>
                <td class="label">Contact:</td>
                <td class="value">{{ $order->parishioner->contact_number ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Address:</td>
                <td class="value" colspan="3">{{ $order->parishioner->address ? $order->parishioner->address . ', ' . $order->parishioner->barangay . ', ' . $order->parishioner->city : '—' }}</td>
            </tr>
        </table>

        {{-- Service info --}}
        <p class="section-title">Service Details</p>
        <table class="info-table">
            <tr>
                <td class="label">Service:</td>
                <td class="value">{{ $order->booking?->getTypeLabel() ?? $order->service?->name ?? '—' }}</td>
                <td class="label">Date:</td>
                <td class="value">{{ $order->booking?->scheduled_date?->format('F d, Y') ?? '—' }}</td>
            </tr>
            @if($order->package)
            <tr>
                <td class="label">Package:</td>
                <td class="value" colspan="3">{{ $order->package->name }}</td>
            </tr>
            @endif
        </table>

        {{-- Line items --}}
        <p class="section-title">Breakdown</p>
        <table class="line-items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->lineItemsList() as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td class="amount">{{ $item['amount'] > 0 ? '&#8369;' . number_format($item['amount'], 2) : '' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>TOTAL AMOUNT DUE</td>
                    <td class="amount">&#8369;{{ number_format($order->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        @if($order->expires_at && $order->status === 'pending')
        <div class="note-box" style="margin-top:8px;">
            <strong>Valid Until:</strong> {{ $order->expires_at->format('F d, Y, g:i A') }}.
            Please complete payment before this date to secure your booking.
        </div>
        @endif

        @if($order->notes)
        <div class="note-box">
            <strong>Notes:</strong> {{ $order->notes }}
        </div>
        @endif

        {{-- Signature line --}}
        <table style="width:100%;margin-top:24px;">
            <tr>
                <td style="width:48%;text-align:center;border-top:0.5pt solid #94a3b8;padding-top:6px;font-size:8pt;color:#64748b;">
                    Parishioner Signature / Date
                </td>
                <td style="width:4%;"></td>
                <td style="width:48%;text-align:center;border-top:0.5pt solid #94a3b8;padding-top:6px;font-size:8pt;color:#64748b;">
                    Parish Office — Authorized Signature
                </td>
            </tr>
        </table>

        <p class="footer">
            {{ $parish['name'] }} &bull; {{ $parish['address'] }} &bull; {{ $parish['phone'] }}<br>
            This is a computer-generated document. For inquiries, contact the parish office.
        </p>
    </div>
</div>

</body>
</html>
