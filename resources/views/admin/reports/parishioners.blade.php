@extends('layouts.app')
@section('title', 'Parishioner Report')
@section('page-title', 'Parishioner Report')

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
            <a href="{{ route('admin.reports.parishioners', request()->query() + ['export'=>'excel']) }}"
               class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg flex items-center gap-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Generate Excel
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn-secondary text-sm">← All Reports</a>
        </form>
    </div>

    {{-- Report content (shown on screen + in print) --}}
    <div id="print-area" class="space-y-5">

        {{-- Print header (hidden on screen, shown when printing) --}}
        <div class="print-header hidden">
            <div class="flex items-center gap-4 mb-4 pb-4 border-b-2 border-blue-700">
                @if(file_exists(public_path('images/parish-logo.png')))
                <img src="{{ asset('images/parish-logo.png') }}" class="w-16 h-16 rounded-full border-2 border-yellow-400">
                @endif
                <div>
                    <h1 class="text-xl font-bold text-blue-800">{{ config('parish.name') }}</h1>
                    <p class="text-sm text-gray-500">{{ config('parish.address') }} · {{ config('parish.phone') }}</p>
                    <p class="text-lg font-bold text-gray-800 mt-1">PARISHIONER REPORT</p>
                </div>
            </div>
        </div>

        {{-- Screen summary row (NO summary cards — removed as requested) --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 no-print">
            <div class="flex flex-wrap gap-6">
                <div><span class="text-xs text-gray-500 uppercase font-medium">Period</span><p class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($data['from'])->format('M d, Y') }} – {{ \Carbon\Carbon::parse($data['to'])->format('M d, Y') }}</p></div>
                <div><span class="text-xs text-gray-500 uppercase font-medium">Total Registered</span><p class="font-bold text-blue-700 text-xl">{{ number_format($data['total']) }}</p></div>
                <div><span class="text-xs text-gray-500 uppercase font-medium">Active</span><p class="font-bold text-green-700 text-xl">{{ number_format($data['active']) }}</p></div>
                <div><span class="text-xs text-gray-500 uppercase font-medium">Families</span><p class="font-bold text-purple-700 text-xl">{{ number_format($data['families']) }}</p></div>
                <div><span class="text-xs text-gray-500 uppercase font-medium">New (Period)</span><p class="font-bold text-amber-700 text-xl">{{ number_format($data['new']) }}</p></div>
            </div>
        </div>

        {{-- Gender Breakdown --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 print-section">
            <h3 class="font-bold text-gray-800 mb-4 text-base border-b border-gray-100 pb-2">Gender Breakdown</h3>
            @php $gTotal = max($data['male'] + $data['female'] + $data['other'], 1); @endphp
            <table class="w-full text-sm">
                <thead><tr class="text-left bg-blue-700 text-white">
                    <th class="px-3 py-2">Gender</th><th class="px-3 py-2 text-right">Count</th><th class="px-3 py-2 text-right">Percentage</th>
                </tr></thead>
                <tbody>
                    @foreach([['Male', $data['male']], ['Female', $data['female']], ['Other / Unknown', $data['other']]] as [$lbl, $val])
                    <tr class="border-b border-gray-50"><td class="px-3 py-2 text-gray-700">{{ $lbl }}</td><td class="px-3 py-2 text-right font-semibold">{{ number_format($val) }}</td><td class="px-3 py-2 text-right text-gray-500">{{ round($val/$gTotal*100,1) }}%</td></tr>
                    @endforeach
                    <tr class="bg-blue-50 font-bold"><td class="px-3 py-2">TOTAL</td><td class="px-3 py-2 text-right">{{ number_format($gTotal) }}</td><td class="px-3 py-2 text-right">100%</td></tr>
                </tbody>
            </table>
            {{-- Screen bar chart --}}
            <div class="mt-4 no-print space-y-2">
                @foreach([['Male',$data['male'],'#3b82f6'],['Female',$data['female'],'#ec4899'],['Other/Unknown',$data['other'],'#9ca3af']] as [$l,$v,$c])
                <div class="flex items-center gap-2"><span class="text-xs w-28 text-gray-500">{{ $l }}</span><div class="flex-1 bg-gray-100 rounded-full h-2"><div class="h-2 rounded-full" style="width:{{ round($v/$gTotal*100) }}%;background:{{ $c }}"></div></div><span class="text-xs font-bold w-10 text-right">{{ round($v/$gTotal*100,1) }}%</span></div>
                @endforeach
            </div>
        </div>

        {{-- Top Barangays --}}
        @if($data['by_barangay']->count())
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 print-section">
            <h3 class="font-bold text-gray-800 mb-4 text-base border-b border-gray-100 pb-2">Top Barangays by Registered Parishioners</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left bg-blue-700 text-white">
                    <th class="px-3 py-2">#</th><th class="px-3 py-2">Barangay</th><th class="px-3 py-2 text-right">Count</th><th class="px-3 py-2 text-right">% Share</th>
                </tr></thead>
                <tbody>
                    @foreach($data['by_barangay'] as $i => $row)
                    <tr class="{{ $i%2===0 ? 'bg-gray-50' : '' }} border-b border-gray-50">
                        <td class="px-3 py-2 text-gray-400 text-center">{{ $i+1 }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $row->barangay ?? 'Unknown' }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ number_format($row->total) }}</td>
                        <td class="px-3 py-2 text-right text-gray-500">{{ $data['total'] > 0 ? round($row->total/$data['total']*100,1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Registration summary (print-only text summary) --}}
        <div class="print-section hidden print-block">
            <div class="border border-gray-200 rounded p-4 text-sm">
                <p class="font-bold mb-2">Registration Summary</p>
                <table class="w-full"><tbody>
                    <tr><td class="py-0.5 text-gray-600 w-48">Total Registered:</td><td class="font-bold">{{ number_format($data['total']) }} parishioners</td></tr>
                    <tr><td class="py-0.5 text-gray-600">Active Members:</td><td class="font-bold">{{ number_format($data['active']) }}</td></tr>
                    <tr><td class="py-0.5 text-gray-600">Inactive:</td><td class="font-bold">{{ number_format($data['inactive']) }}</td></tr>
                    <tr><td class="py-0.5 text-gray-600">Total Families:</td><td class="font-bold">{{ number_format($data['families']) }}</td></tr>
                    <tr><td class="py-0.5 text-gray-600">New Registrations (Period):</td><td class="font-bold">{{ number_format($data['new']) }}</td></tr>
                </tbody></table>
            </div>
        </div>

        {{-- Print signatures --}}
        <div class="print-signatures hidden">
            <div class="grid grid-cols-3 gap-8 mt-8">
                <div class="text-center"><div class="border-t border-gray-800 pt-2"><p class="font-semibold text-sm">Prepared by</p><p class="text-xs text-gray-500">Parish Secretary</p></div></div>
                <div class="text-center"><div class="border-t border-gray-800 pt-2"><p class="font-semibold text-sm">Reviewed by</p><p class="text-xs text-gray-500">Finance Officer</p></div></div>
                <div class="text-center"><div class="border-t border-gray-800 pt-2"><p class="font-semibold text-sm">{{ config('parish.priest') }}</p><p class="text-xs text-gray-500">Parish Priest</p></div></div>
            </div>
        </div>

        {{-- Period/Printed + Copyright — flows naturally after content --}}
        <table id="print-meta" style="display:none;width:100%;border-collapse:collapse;margin-top:14pt;padding-top:6pt;border-top:1pt solid #d1d5db;">
            <tr>
                <td style="font-size:8pt;color:#374151;padding-top:4pt;">
                    {{ config('parish.name') }} &middot; Parishioner Report &middot; Confidential
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

    </div>{{-- /#print-area --}}

</div>
@endsection

@push('scripts')
@if($data['monthly']->count())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@endif
@endpush

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

    /* A4 — extra bottom margin leaves room for fixed copyright footer */
    @page { size: A4 portrait; margin: 15mm 15mm 18mm 15mm; }

    /* Natural page flow — no cut-off rows */
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; break-inside: avoid; }
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }
    .print-section { page-break-inside: avoid; break-inside: avoid; }
    .print-signatures { page-break-inside: avoid; break-inside: avoid; margin-top: 20pt !important; }

    /* Period/Printed — flows naturally, NOT fixed */
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
