@extends('layouts.app')
@section('title', 'Parishioner Report')
@section('page-title', 'Parishioner Report')

@section('content')
<div class="py-6 space-y-5">
    {{-- Filters & Export --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="form-label text-xs">From</label><input type="date" name="from" value="{{ $data['from'] }}" class="form-input text-sm"></div>
            <div><label class="form-label text-xs">To</label><input type="date" name="to" value="{{ $data['to'] }}" class="form-input text-sm"></div>
            <button type="submit" class="action-btn btn-primary btn-sm">Apply</button>
            <a href="{{ route('admin.reports.parishioners', request()->query() + ['export'=>'pdf']) }}" class="action-btn btn-danger btn-sm">Export PDF</a>
            <a href="{{ route('admin.reports.parishioners', request()->query() + ['export'=>'excel']) }}" class="action-btn btn-success btn-sm">Export Excel</a>
            <a href="{{ route('admin.reports.index') }}" class="action-btn btn-ghost btn-sm">← All Reports</a>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wide">Total</p>
            <p class="text-3xl font-bold text-blue-700">{{ number_format($data['total']) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
            <p class="text-xs font-bold text-green-500 uppercase tracking-wide">Active</p>
            <p class="text-3xl font-bold text-green-700">{{ number_format($data['active']) }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-100 rounded-xl p-4 text-center">
            <p class="text-xs font-bold text-purple-500 uppercase tracking-wide">Families</p>
            <p class="text-3xl font-bold text-purple-700">{{ number_format($data['families']) }}</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
            <p class="text-xs font-bold text-amber-500 uppercase tracking-wide">New (Period)</p>
            <p class="text-3xl font-bold text-amber-700">{{ number_format($data['new']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Gender Breakdown --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Gender Breakdown</h3>
            @php $gTotal = $data['male'] + $data['female'] + $data['other']; @endphp
            @foreach([['Male', $data['male'], '#3b82f6'], ['Female', $data['female'], '#ec4899'], ['Other/Unknown', $data['other'], '#9ca3af']] as [$label, $count, $color])
            <div class="mb-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-600">{{ $label }}</span>
                    <span class="text-sm font-bold text-gray-800">{{ number_format($count) }} ({{ $gTotal > 0 ? round($count/$gTotal*100,1) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full" style="width:{{ $gTotal > 0 ? min(100, round($count/$gTotal*100)) : 0 }}%;background:{{ $color }}"></div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Top Barangays --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Top Barangays</h3>
            @if($data['by_barangay']->count())
            @php $maxB = $data['by_barangay']->max('total'); @endphp
            <div class="space-y-2">
                @foreach($data['by_barangay']->take(8) as $row)
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-600 w-36 truncate">{{ $row->barangay ?? 'Unknown' }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full bg-blue-500" style="width:{{ $maxB > 0 ? round($row->total/$maxB*100) : 0 }}%"></div>
                    </div>
                    <span class="text-xs font-bold text-gray-700 w-8 text-right">{{ $row->total }}</span>
                </div>
                @endforeach
            </div>
            @else <p class="text-sm text-gray-400">No data available.</p> @endif
        </div>
    </div>

    {{-- Monthly Registrations --}}
    @if($data['monthly']->count())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Monthly Registrations (Last 12 Months)</h3>
        <div style="position:relative;height:200px;"><canvas id="monthlyChart"></canvas></div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
@if($data['monthly']->count())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const mData  = @json($data['monthly']);
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: mData.map(r => months[r.month-1]+' '+r.year),
        datasets: [{ label: 'New Parishioners', data: mData.map(r => r.total), backgroundColor: '#3b82f6', borderRadius: 4 }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{beginAtZero:true,grid:{color:'rgba(0,0,0,0.04)'}}, x:{grid:{display:false}} } }
});
</script>
@endif
@endpush
