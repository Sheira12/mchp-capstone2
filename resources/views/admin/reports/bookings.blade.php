@extends('layouts.app')
@section('title', 'Booking Report')
@section('page-title', 'Booking Report')

@section('content')
<div class="py-6 space-y-5">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="form-label text-xs">From</label><input type="date" name="from" value="{{ $data['from'] }}" class="form-input text-sm"></div>
            <div><label class="form-label text-xs">To</label><input type="date" name="to" value="{{ $data['to'] }}" class="form-input text-sm"></div>
            <button type="submit" class="action-btn btn-primary btn-sm">Apply</button>
            <a href="{{ route('admin.reports.bookings', request()->query() + ['export'=>'pdf']) }}" class="action-btn btn-danger btn-sm">Export PDF</a>
            <a href="{{ route('admin.reports.bookings', request()->query() + ['export'=>'excel']) }}" class="action-btn btn-success btn-sm">Export Excel</a>
            <a href="{{ route('admin.reports.index') }}" class="action-btn btn-ghost btn-sm">← All Reports</a>
        </form>
    </div>

    {{-- Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        @foreach([
            ['Total', $data['total'], 'gray'],
            ['Pending', $data['pending'], 'amber'],
            ['Confirmed', $data['confirmed'], 'green'],
            ['Completed', $data['completed'], 'blue'],
            ['Cancelled', $data['cancelled'], 'red'],
        ] as [$lbl, $val, $color])
        <div class="bg-{{ $color }}-50 border border-{{ $color }}-100 rounded-xl p-4 text-center">
            <p class="text-xs font-bold text-{{ $color }}-500 uppercase tracking-wide">{{ $lbl }}</p>
            <p class="text-2xl font-bold text-{{ $color }}-700">{{ number_format($val) }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- By Type --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Bookings by Service Type</h3>
            @if(count($data['by_type']))
            @php $maxT = collect($data['by_type'])->max('total'); @endphp
            <div class="space-y-2">
                @foreach($data['by_type'] as $t)
                <div>
                    <div class="flex items-center justify-between mb-1 text-sm">
                        <span class="text-gray-700">{{ $t['type'] }}</span>
                        <span class="font-bold text-gray-900">{{ $t['total'] }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-amber-500" style="width:{{ $maxT > 0 ? round($t['total']/$maxT*100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else <p class="text-sm text-gray-400">No bookings in this period.</p> @endif
        </div>

        {{-- Monthly trend --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Monthly Booking Trend</h3>
            <div style="position:relative;height:200px;"><canvas id="bookChart"></canvas></div>
        </div>
    </div>

    {{-- Revenue from bookings --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Revenue from Completed Bookings (Period)</p>
            <p class="text-3xl font-bold text-green-600">₱{{ number_format($data['revenue'], 2) }}</p>
        </div>
        <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const bData = @json($data['monthly']);
new Chart(document.getElementById('bookChart'), {
    type: 'bar',
    data: {
        labels: bData.map(r => months[r.month-1]+' '+r.year),
        datasets: [{ label: 'Bookings', data: bData.map(r => r.total), backgroundColor: '#f59e0b', borderRadius: 4 }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{beginAtZero:true}, x:{grid:{display:false}} } }
});
</script>
@endpush
