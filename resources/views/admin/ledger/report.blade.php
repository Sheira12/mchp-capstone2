@extends('layouts.app')
@section('title', 'Financial Report')
@section('page-title', 'Financial Report')

@section('content')
<div class="py-6 pb-20 space-y-5">

    {{-- Filters & Actions --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 no-print">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="form-label text-xs">From</label><input type="date" name="from" value="{{ $from }}" class="form-input text-sm"></div>
            <div><label class="form-label text-xs">To</label><input type="date" name="to" value="{{ $to }}" class="form-input text-sm"></div>
            <button type="submit" class="btn-primary text-sm px-4 py-2">Apply</button>
            <button type="button" onclick="window.print()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Report
            </button>
            <a href="{{ route('admin.ledger.report', request()->query() + ['export'=>'pdf']) }}"
               class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Export PDF
            </a>
            <a href="{{ route('admin.ledger.index') }}" class="btn-secondary text-sm">← Ledger</a>
        </form>
    </div>

    {{-- ── PRINT AREA ── --}}
    <div id="print-area" class="space-y-5">

        {{-- Print header --}}
        <div class="print-header hidden">
            <div class="flex items-center gap-4 mb-4 pb-4 border-b-2 border-purple-700">
                @if(file_exists(public_path('images/parish-logo.png')))
                <img src="{{ asset('images/parish-logo.png') }}" class="w-16 h-16 rounded-full border-2 border-yellow-400">
                @endif
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-purple-800">{{ $parish['name'] }}</h1>
                    <p class="text-sm text-gray-500">{{ $parish['address'] }} · {{ $parish['phone'] }}</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">FINANCIAL REPORT — CREDIT &amp; DEBIT STATEMENT</p>
                    <p class="text-sm text-gray-500">Period: {{ \Carbon\Carbon::parse($from)->format('M d, Y') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }} &nbsp;|&nbsp; Printed: {{ $printedAt }}</p>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-green-50 border border-green-200 rounded-xl p-5">
                <p class="text-xs font-bold text-green-600 uppercase tracking-wide mb-1">Total Income (Credit)</p>
                <p class="text-3xl font-bold text-green-700">₱{{ number_format($totalCredit, 2) }}</p>
                <p class="text-xs text-green-500 mt-1">{{ $entries->where('type','credit')->count() }} transaction(s)</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-5">
                <p class="text-xs font-bold text-red-600 uppercase tracking-wide mb-1">Total Expenses (Debit)</p>
                <p class="text-3xl font-bold text-red-700">₱{{ number_format($totalDebit, 2) }}</p>
                <p class="text-xs text-red-500 mt-1">{{ $entries->where('type','debit')->count() }} transaction(s)</p>
            </div>
            <div class="{{ $netBalance >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200' }} border rounded-xl p-5">
                <p class="text-xs font-bold {{ $netBalance >= 0 ? 'text-blue-600' : 'text-red-600' }} uppercase tracking-wide mb-1">
                    Net Balance {{ $netBalance < 0 ? '(Deficit)' : '(Surplus)' }}
                </p>
                <p class="text-3xl font-bold {{ $netBalance >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                    {{ $netBalance < 0 ? '-' : '' }}₱{{ number_format(abs($netBalance), 2) }}
                </p>
            </div>
        </div>

        {{-- Summary by Category --}}
        @if($byCategory->count())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden print-section">
            <div class="px-5 py-3 border-b border-gray-100 bg-purple-700">
                <h3 class="font-bold text-white">Summary by Category</h3>
            </div>
            <table class="w-full text-sm">
                <thead><tr class="bg-purple-800 text-white text-left">
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2 text-right">Entries</th>
                    <th class="px-4 py-2 text-right">Total Amount</th>
                </tr></thead>
                <tbody>
                    @foreach($byCategory as $cat => $info)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : '' }} border-b border-gray-50">
                        <td class="px-4 py-2 font-medium text-gray-800">{{ $cat }}</td>
                        <td class="px-4 py-2">
                            @if($info['type']==='credit')
                            <span class="text-xs font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded-full">Income</span>
                            @else
                            <span class="text-xs font-bold text-red-700 bg-red-100 px-2 py-0.5 rounded-full">Expense</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right text-gray-500">{{ $info['count'] }}</td>
                        <td class="px-4 py-2 text-right font-bold {{ $info['type']==='credit' ? 'text-green-700' : 'text-red-700' }}">
                            ₱{{ number_format($info['total'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Detailed Transactions --}}
        @if($entries->count())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden print-section mb-8">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-700">
                <h3 class="font-bold text-white">Detailed Transactions</h3>
            </div>
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-800 text-white text-left">
                    <th class="px-3 py-2">Date</th>
                    <th class="px-3 py-2">Type</th>
                    <th class="px-3 py-2">Category</th>
                    <th class="px-3 py-2">Description</th>
                    <th class="px-3 py-2 no-print">Ref #</th>
                    <th class="px-3 py-2 text-right">Amount</th>
                </tr></thead>
                <tbody>
                    @foreach($entries as $i => $entry)
                    <tr class="{{ $i%2===0 ? 'bg-gray-50' : '' }} border-b border-gray-50">
                        <td class="px-3 py-2 text-gray-600 text-xs whitespace-nowrap">{{ $entry->entry_date->format('M d, Y') }}</td>
                        <td class="px-3 py-2">
                            @if($entry->type==='credit')
                            <span class="text-xs font-bold text-green-700">↑ Income</span>
                            @else
                            <span class="text-xs font-bold text-red-700">↓ Expense</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-600 text-xs">{{ $entry->category }}</td>
                        <td class="px-3 py-2 text-gray-800">{{ $entry->description }}</td>
                        <td class="px-3 py-2 text-gray-400 font-mono text-xs no-print">{{ $entry->reference_number ?? '—' }}</td>
                        <td class="px-3 py-2 text-right font-bold {{ $entry->type==='credit' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $entry->type==='credit' ? '+' : '-' }}₱{{ number_format($entry->amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-400">
                        <td colspan="4" class="px-3 py-2 text-right">Total Income:</td>
                        <td colspan="2" class="px-3 py-2 text-right text-green-700">+₱{{ number_format($totalCredit,2) }}</td>
                    </tr>
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="4" class="px-3 py-2 text-right">Total Expenses:</td>
                        <td colspan="2" class="px-3 py-2 text-right text-red-700">-₱{{ number_format($totalDebit,2) }}</td>
                    </tr>
                    <tr class="{{ $netBalance >= 0 ? 'bg-blue-100' : 'bg-red-100' }} font-bold border-t border-gray-300">
                        <td colspan="4" class="px-3 py-2 text-right text-base">NET BALANCE:</td>
                        <td colspan="2" class="px-3 py-2 text-right text-base {{ $netBalance >= 0 ? 'text-blue-800' : 'text-red-800' }}">
                            {{ $netBalance < 0 ? '-' : '+' }}₱{{ number_format(abs($netBalance),2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center text-gray-400">
            <p class="font-medium">No transactions in the selected period.</p>
        </div>
        @endif

        {{-- Print signatures --}}
        <div class="print-signatures hidden">
            <div class="grid grid-cols-3 gap-8 mt-8">
                <div class="text-center"><div class="border-t border-gray-800 pt-2"><p class="font-semibold text-sm">Prepared by</p><p class="text-xs text-gray-500">Parish Secretary</p></div></div>
                <div class="text-center"><div class="border-t border-gray-800 pt-2"><p class="font-semibold text-sm">Verified by</p><p class="text-xs text-gray-500">Finance Officer</p></div></div>
                <div class="text-center"><div class="border-t border-gray-800 pt-2"><p class="font-semibold text-sm">{{ config('parish.priest') }}</p><p class="text-xs text-gray-500">Parish Priest</p></div></div>
            </div>
            <p class="text-center text-xs text-gray-400 mt-6">This is an official financial document of {{ $parish['name'] }}. Generated on {{ $printedAt }}</p>
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
