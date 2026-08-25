@extends('layouts.app')
@section('title', 'Booking Report')
@section('page-title', 'Booking Report')

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
            <a href="{{ route('admin.reports.bookings', request()->query() + ['export'=>'excel']) }}"
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Generate Excel
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn-secondary text-sm">← All Reports</a>
        </form>
    </div>

    <div id="print-area" class="space-y-5">

        {{-- Print header --}}
        <div class="print-header hidden">
            <div class="flex items-center gap-4 mb-4 pb-4 border-b-2 border-amber-600">
                @if(file_exists(public_path('images/parish-logo.png')))
                <img src="{{ asset('images/parish-logo.png') }}" class="w-16 h-16 rounded-full border-2 border-yellow-400">
                @endif
                <div>
                    <h1 class="text-xl font-bold text-amber-800">{{ config('parish.name') }}</h1>
                    <p class="text-sm text-gray-500">{{ config('parish.address') }} · {{ config('parish.phone') }}</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">BOOKING REPORT</p>
                    <p class="text-sm text-gray-500">Period: {{ \Carbon\Carbon::parse($data['from'])->format('M d, Y') }} – {{ \Carbon\Carbon::parse($data['to'])->format('M d, Y') }} | Printed: {{ now()->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        {{-- Status Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            @foreach([
                ['Total','gray',$data['total']],
                ['Pending','amber',$data['pending']],
                ['Confirmed','green',$data['confirmed']],
                ['Completed','blue',$data['completed']],
                ['Cancelled','red',$data['cancelled']],
            ] as [$lbl, $color, $val])
            <div class="bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-xl p-3 text-center">
                <p class="text-xs font-bold text-{{ $color }}-600 uppercase tracking-wide">{{ $lbl }}</p>
                <p class="text-2xl font-bold text-{{ $color }}-700 mt-1">{{ number_format($val) }}</p>
            </div>
            @endforeach
        </div>

        {{-- By Service Type --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 print-section">
            <h3 class="font-bold text-gray-800 mb-4 text-base border-b border-gray-100 pb-2">Bookings by Service Type</h3>
            @if(count($data['by_type']))
            <table class="w-full text-sm">
                <thead><tr class="bg-amber-600 text-white text-left">
                    <th class="px-3 py-2">Service Type</th>
                    <th class="px-3 py-2 text-right">Count</th>
                    <th class="px-3 py-2 text-right">% Share</th>
                </tr></thead>
                <tbody>
                    @php $gtotal = max($data['total'], 1); @endphp
                    @foreach($data['by_type'] as $i => $t)
                    <tr class="{{ $i%2===0 ? 'bg-gray-50' : '' }} border-b border-gray-50">
                        <td class="px-3 py-2 text-gray-700">{{ $t['type'] }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ number_format($t['total']) }}</td>
                        <td class="px-3 py-2 text-right text-gray-500">{{ round($t['total']/$gtotal*100,1) }}%</td>
                    </tr>
                    @endforeach
                    <tr class="bg-amber-50 font-bold border-t-2 border-amber-500">
                        <td class="px-3 py-2">TOTAL</td>
                        <td class="px-3 py-2 text-right">{{ number_format($data['total']) }}</td>
                        <td class="px-3 py-2 text-right">100%</td>
                    </tr>
                </tbody>
            </table>
            {{-- Screen bar chart --}}
            <div class="mt-4 no-print space-y-2">
                @foreach($data['by_type'] as $t)
                @php $pct = $gtotal > 0 ? round($t['total']/$gtotal*100) : 0; @endphp
                <div class="flex items-center gap-2">
                    <span class="text-xs w-40 truncate text-gray-600">{{ $t['type'] }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-2"><div class="h-2 rounded-full bg-amber-500" style="width:{{ $pct }}%"></div></div>
                    <span class="text-xs font-bold w-8 text-right">{{ $t['total'] }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 py-4 text-center">No bookings in this period.</p>
            @endif
        </div>

        {{-- Status Breakdown --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 print-section">
            <h3 class="font-bold text-gray-800 mb-4 text-base border-b border-gray-100 pb-2">Status Breakdown</h3>
            <table class="w-full text-sm">
                <thead><tr class="bg-amber-700 text-white text-left">
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2 text-right">Count</th>
                    <th class="px-3 py-2 text-right">% of Total</th>
                </tr></thead>
                <tbody>
                    @foreach(['Pending'=>$data['pending'],'Confirmed'=>$data['confirmed'],'Completed'=>$data['completed'],'Cancelled'=>$data['cancelled']] as $s => $c)
                    <tr class="border-b border-gray-50">
                        <td class="px-3 py-2 text-gray-700">{{ $s }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ number_format($c) }}</td>
                        <td class="px-3 py-2 text-right text-gray-500">{{ $data['total']>0 ? round($c/$data['total']*100,1) : 0 }}%</td>
                    </tr>
                    @endforeach
                    <tr class="bg-amber-50 font-bold border-t-2 border-amber-500">
                        <td class="px-3 py-2">TOTAL</td>
                        <td class="px-3 py-2 text-right">{{ number_format($data['total']) }}</td>
                        <td class="px-3 py-2 text-right">100%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Revenue --}}
        <div class="bg-green-50 border border-green-200 rounded-xl p-5 print-section">
            <p class="text-sm font-semibold text-green-700 uppercase tracking-wide mb-1">Revenue from Completed Bookings (Period)</p>
            <p class="text-3xl font-bold text-green-800">₱{{ number_format($data['revenue'], 2) }}</p>
            <p class="text-xs text-green-600 mt-1">Based on {{ number_format($data['completed']) }} completed booking(s)</p>
        </div>

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
