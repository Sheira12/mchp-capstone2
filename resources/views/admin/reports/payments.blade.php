@extends('layouts.app')
@section('title', 'Payment Report')
@section('page-title', 'Payment Report')

@section('content')
<div class="py-6 space-y-5">

    {{-- Filters & Actions --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 no-print">
        <form method="GET" class="space-y-3">

            {{-- Quarter selector --}}
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Quick Quarter Select</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($data['quarters'] as $qKey => $qInfo)
                    <a href="{{ route('admin.reports.payments', ['quarter' => $qKey, 'year' => $data['year']]) }}"
                       class="px-3 py-1.5 rounded-lg text-sm font-semibold border transition
                           {{ $data['quarter'] === $qKey
                               ? 'bg-blue-600 text-white border-blue-600'
                               : 'bg-white text-gray-600 border-gray-200 hover:border-blue-400 hover:text-blue-600' }}">
                        {{ $qKey === 'q1' ? 'Q1 Jan–Mar' : ($qKey === 'q2' ? 'Q2 Apr–Jun' : ($qKey === 'q3' ? 'Q3 Jul–Sep' : 'Q4 Oct–Dec')) }}
                    </a>
                    @endforeach
                    @if($data['quarter'])
                    <a href="{{ route('admin.reports.payments') }}"
                       class="px-3 py-1.5 rounded-lg text-sm font-semibold border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-300 transition">
                        ✕ Clear
                    </a>
                    @endif
                </div>
            </div>

            {{-- Year + custom date range --}}
            <div class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="form-label text-xs">Year</label>
                    <select name="year" class="form-select text-sm">
                        @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" @selected($data['year'] == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">From</label>
                    <input type="date" name="from" value="{{ $data['from'] }}" class="form-input text-sm">
                </div>
                <div>
                    <label class="form-label text-xs">To</label>
                    <input type="date" name="to" value="{{ $data['to'] }}" class="form-input text-sm">
                </div>
                <button type="submit" class="btn-primary text-sm px-4 py-2">Apply</button>

                {{-- Export buttons --}}
                <button type="button" onclick="window.print()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print
                </button>
                <a href="{{ route('admin.reports.payments', array_merge(request()->query(), ['export' => 'pdf', 'quarter' => $data['quarter'], 'year' => $data['year']])) }}"
                   class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export PDF
                </a>
                <a href="{{ route('admin.reports.payments', array_merge(request()->query(), ['export' => 'excel', 'quarter' => $data['quarter'], 'year' => $data['year']])) }}"
                   class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    Export Excel
                </a>
                <a href="{{ route('admin.reports.index') }}" class="btn-secondary text-sm">← All Reports</a>
            </div>
        </form>
    </div>

    <div id="print-area" class="space-y-5">

        {{-- Print header --}}
        <div class="print-header hidden">
            <div class="flex items-center gap-4 mb-4 pb-4 border-b-2 border-green-700">
                @if(file_exists(public_path('images/parish-logo.png')))
                <img src="{{ asset('images/parish-logo.png') }}" class="w-16 h-16 rounded-full border-2 border-yellow-400">
                @endif
                <div>
                    <h1 class="text-xl font-bold text-green-800">{{ config('parish.name') }}</h1>
                    <p class="text-sm text-gray-500">{{ config('parish.address') }} · {{ config('parish.phone') }}</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">PAYMENT REPORT{{ $data['quarter_label'] ? ' — ' . strtoupper($data['quarter_label']) : '' }}</p>
                </div>
            </div>
        </div>

        {{-- Quarter label banner --}}
        @if($data['quarter_label'])
        <div class="bg-blue-700 text-white rounded-xl px-5 py-3 flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <div>
                <p class="font-bold text-base">{{ $data['quarter_label'] }}</p>
                <p class="text-blue-200 text-xs">{{ \Carbon\Carbon::parse($data['from'])->format('F d') }} – {{ \Carbon\Carbon::parse($data['to'])->format('F d, Y') }}</p>
            </div>
        </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                <p class="text-xs font-bold text-green-600 uppercase tracking-wide">Total Collected</p>
                <p class="text-2xl font-bold text-green-700 mt-1">₱{{ number_format($data['total_collected'], 0) }}</p>
                <p class="text-xs text-green-500 mt-1">{{ ($data['debit_count'] + $data['credit_count']) }} transactions</p>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-center">
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wide">Pending</p>
                <p class="text-2xl font-bold text-amber-700 mt-1">₱{{ number_format($data['total_pending'], 0) }}</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
                <p class="text-xs font-bold text-red-600 uppercase tracking-wide">Outstanding</p>
                <p class="text-2xl font-bold text-red-700 mt-1">₱{{ number_format($data['outstanding_amt'], 0) }}</p>
                <p class="text-xs text-red-400">{{ $data['outstanding_count'] }} bookings</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                <p class="text-xs font-bold text-blue-600 uppercase tracking-wide">Refunded</p>
                <p class="text-2xl font-bold text-blue-700 mt-1">₱{{ number_format($data['total_refunded'], 0) }}</p>
            </div>
        </div>

        {{-- Debit / Credit Breakdown --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border-l-4 border-red-500 border border-gray-100 shadow-sm p-5">
                <div class="mb-2">
                    <p class="text-xs font-bold text-red-500 uppercase tracking-wide">Total Debit</p>
                    <p class="text-xs text-gray-400">Payments made by parishioners</p>
                </div>
                <p class="text-3xl font-extrabold text-red-600">₱{{ number_format($data['total_debit'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $data['debit_count'] }} debit transaction(s)</p>
            </div>
            <div class="bg-white rounded-xl border-l-4 border-green-500 border border-gray-100 shadow-sm p-5">
                <div class="mb-2">
                    <p class="text-xs font-bold text-green-500 uppercase tracking-wide">Total Credit</p>
                    <p class="text-xs text-gray-400">Refunds / adjustments returned</p>
                </div>
                <p class="text-3xl font-extrabold text-green-600">₱{{ number_format($data['total_credit'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $data['credit_count'] }} credit transaction(s)</p>
            </div>
        </div>

        {{-- Collections by Method --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 print-section">
            <h3 class="font-bold text-gray-800 mb-4 text-base border-b border-gray-100 pb-2">Collections by Payment Method</h3>
            @if($data['by_method']->count())
            <table class="w-full text-sm">
                <thead><tr class="bg-green-700 text-white text-left">
                    <th class="px-3 py-2">Payment Method</th>
                    <th class="px-3 py-2 text-right">Transactions</th>
                    <th class="px-3 py-2 text-right">Total (PHP)</th>
                    <th class="px-3 py-2 text-right">% Share</th>
                </tr></thead>
                <tbody>
                    @php $cTotal = max($data['total_collected'], 1); @endphp
                    @foreach($data['by_method'] as $i => $m)
                    <tr class="{{ $i%2===0 ? 'bg-gray-50' : '' }} border-b border-gray-50">
                        <td class="px-3 py-2 font-medium text-gray-700">{{ \App\Models\Payment::METHODS[$m->payment_method] ?? ucfirst($m->payment_method) }}</td>
                        <td class="px-3 py-2 text-right">{{ number_format($m->count) }}</td>
                        <td class="px-3 py-2 text-right font-semibold text-green-700">₱{{ number_format($m->total, 2) }}</td>
                        <td class="px-3 py-2 text-right text-gray-500">{{ round($m->total/$cTotal*100,1) }}%</td>
                    </tr>
                    @endforeach
                    <tr class="bg-green-50 font-bold border-t-2 border-green-600">
                        <td class="px-3 py-2">TOTAL COLLECTED</td>
                        <td class="px-3 py-2 text-right">{{ $data['by_method']->sum('count') }}</td>
                        <td class="px-3 py-2 text-right text-green-700">₱{{ number_format($data['total_collected'], 2) }}</td>
                        <td class="px-3 py-2 text-right">100%</td>
                    </tr>
                </tbody>
            </table>
            {{-- Screen bar chart --}}
            <div class="mt-4 no-print space-y-2">
                @foreach($data['by_method'] as $m)
                @php $pct = $cTotal > 0 ? round($m->total/$cTotal*100) : 0; @endphp
                <div class="flex items-center gap-2">
                    <span class="text-xs w-36 truncate text-gray-600">{{ \App\Models\Payment::METHODS[$m->payment_method] ?? $m->payment_method }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-2"><div class="h-2 rounded-full bg-green-500" style="width:{{ $pct }}%"></div></div>
                    <span class="text-xs font-bold w-14 text-right">₱{{ number_format($m->total,0) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 py-4 text-center">No payments in this period.</p>
            @endif
        </div>

        {{-- Debit / Credit Transaction Summary table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 print-section">
            <h3 class="font-bold text-gray-800 mb-4 text-base border-b border-gray-100 pb-2">Transaction Type Summary</h3>
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-800 text-white text-left">
                    <th class="px-3 py-2">Transaction Type</th>
                    <th class="px-3 py-2 text-right">Count</th>
                    <th class="px-3 py-2 text-right">Total Amount (PHP)</th>
                    <th class="px-3 py-2 text-right">Description</th>
                </tr></thead>
                <tbody>
                    <tr class="bg-red-50 border-b border-gray-100">
                        <td class="px-3 py-2 font-bold text-red-700">Debit</td>
                        <td class="px-3 py-2 text-right">{{ number_format($data['debit_count']) }}</td>
                        <td class="px-3 py-2 text-right font-bold text-red-700">₱{{ number_format($data['total_debit'], 2) }}</td>
                        <td class="px-3 py-2 text-right text-gray-500 text-xs">Fees paid by parishioners</td>
                    </tr>
                    <tr class="bg-green-50 border-b border-gray-100">
                        <td class="px-3 py-2 font-bold text-green-700">Credit</td>
                        <td class="px-3 py-2 text-right">{{ number_format($data['credit_count']) }}</td>
                        <td class="px-3 py-2 text-right font-bold text-green-700">₱{{ number_format($data['total_credit'], 2) }}</td>
                        <td class="px-3 py-2 text-right text-gray-500 text-xs">Refunds / adjustments</td>
                    </tr>
                    <tr class="bg-blue-50 font-bold border-t-2 border-blue-600">
                        <td class="px-3 py-2 text-blue-900">NET TOTAL</td>
                        <td class="px-3 py-2 text-right text-blue-900">{{ number_format($data['debit_count'] + $data['credit_count']) }}</td>
                        <td class="px-3 py-2 text-right text-blue-900">₱{{ number_format($data['total_debit'] - $data['total_credit'], 2) }}</td>
                        <td class="px-3 py-2 text-right text-gray-400 text-xs">Debit minus Credit</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Daily Collections --}}
        @if($data['daily']->count())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden print-section">
            <div class="px-5 py-3 border-b border-gray-100 bg-green-700">
                <h3 class="font-bold text-white">Daily Collections Summary</h3>
            </div>
            <table class="w-full text-sm">
                <thead><tr class="bg-green-800 text-white text-left">
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2 text-right">Amount Collected (PHP)</th>
                    <th class="px-4 py-2 text-right no-print">Transactions</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($data['daily'] as $i => $d)
                    <tr class="{{ $i%2===0 ? 'bg-gray-50' : '' }}">
                        <td class="px-4 py-2 text-gray-700">{{ \Carbon\Carbon::parse($d->date)->format('M d, Y (l)') }}</td>
                        <td class="px-4 py-2 text-right font-semibold text-green-700">₱{{ number_format($d->total, 2) }}</td>
                        <td class="px-4 py-2 text-right text-gray-400 no-print">{{ $d->count ?? '—' }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-green-50 font-bold border-t-2 border-green-600">
                        <td class="px-4 py-2">SUBTOTAL</td>
                        <td class="px-4 py-2 text-right text-green-700">₱{{ number_format($data['daily']->sum('total'), 2) }}</td>
                        <td class="px-4 py-2 text-right no-print">{{ $data['daily']->sum('count') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        {{-- Print signatures --}}
        <div class="print-signatures hidden">
            <div class="grid grid-cols-3 gap-8 mt-8">
                <div class="text-center"><div class="border-t border-gray-800 pt-2"><p class="font-semibold text-sm">Prepared by</p><p class="text-xs text-gray-500">Parish Secretary</p></div></div>
                <div class="text-center"><div class="border-t border-gray-800 pt-2"><p class="font-semibold text-sm">Verified by</p><p class="text-xs text-gray-500">Finance Officer</p></div></div>
                <div class="text-center"><div class="border-t border-gray-800 pt-2"><p class="font-semibold text-sm">{{ config('parish.priest') }}</p><p class="text-xs text-gray-500">Parish Priest</p></div></div>
            </div>
        </div>

        {{-- Period/Printed — flows naturally after signatures, NOT fixed --}}
        <table id="print-meta" style="display:none;width:100%;border-collapse:collapse;margin-top:14pt;padding-top:6pt;border-top:1pt solid #d1d5db;">
            <tr>
                <td style="font-size:8pt;color:#374151;padding-top:4pt;">
                    {{ config('parish.name') }} &middot; Payment Report &middot; Confidential
                </td>
                <td style="font-size:8pt;color:#374151;padding-top:4pt;text-align:right;">
                    Period: {{ \Carbon\Carbon::parse($data['from'])->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($data['to'])->format('M d, Y') }}
                    &nbsp;|&nbsp; Printed: {{ now()->format('M d, Y h:i A') }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size:7pt;color:#9ca3af;text-align:center;padding-top:6pt;border-top:0.5pt solid #e5e7eb;">
                    &copy; {{ date('Y') }} {{ config('parish.name') }} &mdash; All rights reserved.
                </td>
            </tr>
        </table>

    </div>
</div>

@endsection

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    nav, aside, header, [data-sidebar], .sidebar { display: none !important; }
    .print-header     { display: flex !important; }
    .print-block      { display: block !important; }
    .print-signatures { display: grid !important; }
    body, html { background: white !important; }
    .py-6 { padding: 0 !important; }
    .space-y-5 > * + * { margin-top: 10pt; }
    .bg-white { box-shadow: none !important; }
    table thead tr { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    tr.bg-gray-50, tr.bg-red-50, tr.bg-green-50, tr.bg-blue-50, tr.bg-amber-50 {
        -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { size: A4 portrait; margin: 15mm 15mm 18mm 15mm; }
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; break-inside: avoid; }
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
    .print-section { page-break-inside: avoid; break-inside: avoid; }
    .print-signatures { page-break-inside: avoid; break-inside: avoid; margin-top: 20pt !important; }
    .space-y-5, #print-area { page-break-after: auto; }
    /* Period/Printed — flows in content */
    #print-meta { display: table !important; }
}
</style>
@endpush

@push('scripts')
<script>
window.addEventListener('beforeprint', function () {
    document.querySelectorAll('nav, aside, header, [data-sidebar], .sidebar, .no-print')
        .forEach(el => { el.dataset.hiddenForPrint = '1'; el.style.display = 'none'; });
    document.getElementById('print-area')?.style.setProperty('display', 'block', 'important');
    document.getElementById('print-meta')?.style.setProperty('display', 'table', 'important');
});
window.addEventListener('afterprint', function () {
    document.querySelectorAll('[data-hidden-for-print]')
        .forEach(el => { el.style.display = ''; delete el.dataset.hiddenForPrint; });
    document.getElementById('print-meta')?.style.setProperty('display', 'none', 'important');
});
</script>
@endpush
