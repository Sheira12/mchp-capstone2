@extends('layouts.app')
@section('title', 'Payment Report')
@section('page-title', 'Payment Report')

@section('content')
<div class="py-6 space-y-5">

    {{-- Filters & Actions --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 no-print">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="form-label text-xs">From</label><input type="date" name="from" value="{{ $data['from'] }}" class="form-input text-sm"></div>
            <div><label class="form-label text-xs">To</label><input type="date" name="to" value="{{ $data['to'] }}" class="form-input text-sm"></div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Apply</button>
            <button type="button" onclick="window.print()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Report
            </button>
            <a href="{{ route('admin.reports.payments', request()->query() + ['export'=>'excel']) }}"
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Export Excel
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn-secondary text-sm">← All Reports</a>
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
                    <p class="text-lg font-bold text-gray-800 mt-1">PAYMENT REPORT</p>
                    <p class="text-sm text-gray-500">Period: {{ \Carbon\Carbon::parse($data['from'])->format('M d, Y') }} – {{ \Carbon\Carbon::parse($data['to'])->format('M d, Y') }} | Printed: {{ now()->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                <p class="text-xs font-bold text-green-600 uppercase tracking-wide">Total Collected</p>
                <p class="text-2xl font-bold text-green-700 mt-1">₱{{ number_format($data['total_collected'], 0) }}</p>
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
                    <span class="text-xs w-28 truncate text-gray-600">{{ \App\Models\Payment::METHODS[$m->payment_method] ?? $m->payment_method }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-2"><div class="h-2 rounded-full bg-green-500" style="width:{{ $pct }}%"></div></div>
                    <span class="text-xs font-bold w-14 text-right">₱{{ number_format($m->total,0) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 py-4 text-center">No payments in this period.</p>
            @endif
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
    tr.bg-gray-50, tr.bg-amber-50, tr.bg-green-50, tr.bg-blue-50 {
        -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { size: A4 portrait; margin: 15mm 12mm; }
}
</style>
@endpush


@push('scripts')
<script>
// Hide layout chrome on print, restore after
window.addEventListener('beforeprint', function() {
    document.querySelectorAll('nav, aside, header, [data-sidebar], .sidebar, .no-print')
        .forEach(el => { el.dataset.hiddenForPrint = '1'; el.style.display = 'none'; });
    document.getElementById('print-area')?.style.setProperty('display', 'block', 'important');
});
window.addEventListener('afterprint', function() {
    document.querySelectorAll('[data-hidden-for-print]')
        .forEach(el => { el.style.display = ''; delete el.dataset.hiddenForPrint; });
});
</script>
@endpush
