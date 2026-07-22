@extends('layouts.app')
@section('title', 'Payment Report')
@section('page-title', 'Payment Report')

@section('content')
<div class="py-6 space-y-5">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div><label class="form-label text-xs">From</label><input type="date" name="from" value="{{ $data['from'] }}" class="form-input text-sm"></div>
            <div><label class="form-label text-xs">To</label><input type="date" name="to" value="{{ $data['to'] }}" class="form-input text-sm"></div>
            <button type="submit" class="action-btn btn-primary btn-sm">Apply</button>
            <a href="{{ route('admin.reports.payments', request()->query() + ['export'=>'pdf']) }}" class="action-btn btn-danger btn-sm">Export PDF</a>
            <a href="{{ route('admin.reports.payments', request()->query() + ['export'=>'excel']) }}" class="action-btn btn-success btn-sm">Export Excel</a>
            <a href="{{ route('admin.reports.index') }}" class="action-btn btn-ghost btn-sm">← All Reports</a>
        </form>
    </div>

    {{-- Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
            <p class="text-xs font-bold text-green-500 uppercase tracking-wide">Collected</p>
            <p class="text-2xl font-bold text-green-700">₱{{ number_format($data['total_collected'], 0) }}</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
            <p class="text-xs font-bold text-amber-500 uppercase tracking-wide">Pending</p>
            <p class="text-2xl font-bold text-amber-700">₱{{ number_format($data['total_pending'], 0) }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
            <p class="text-xs font-bold text-red-500 uppercase tracking-wide">Outstanding</p>
            <p class="text-2xl font-bold text-red-700">₱{{ number_format($data['outstanding_amt'], 0) }}</p>
            <p class="text-xs text-red-400">{{ $data['outstanding_count'] }} bookings</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wide">Refunded</p>
            <p class="text-2xl font-bold text-blue-700">₱{{ number_format($data['total_refunded'], 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- By Method --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Collections by Payment Method</h3>
            @if($data['by_method']->count())
            @php $maxM = $data['by_method']->max('total'); @endphp
            <div class="space-y-3">
                @foreach($data['by_method'] as $m)
                <div>
                    <div class="flex items-center justify-between mb-1 text-sm">
                        <span class="text-gray-700 font-medium capitalize">{{ \App\Models\Payment::METHODS[$m->payment_method] ?? $m->payment_method }}</span>
                        <span class="font-bold text-gray-900">₱{{ number_format($m->total, 0) }} <span class="text-xs text-gray-400">({{ $m->count }})</span></span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full bg-green-500" style="width:{{ $maxM > 0 ? round($m->total/$maxM*100) : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else <p class="text-sm text-gray-400">No payments in this period.</p> @endif
        </div>

        {{-- Monthly trend --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Monthly Revenue Trend</h3>
            <div style="position:relative;height:180px;"><canvas id="revChart"></canvas></div>
        </div>
    </div>

    {{-- Daily table --}}
    @if($data['daily']->count())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Daily Collections</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr class="text-left text-gray-500"><th class="px-4 py-2 font-medium">Date</th><th class="px-4 py-2 font-medium text-right">Total Collected</th></tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($data['daily'] as $d)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-700">{{ \Carbon\Carbon::parse($d->date)->format('M d, Y (l)') }}</td>
                        <td class="px-4 py-2 text-right font-semibold text-green-600">₱{{ number_format($d->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const rData = @json($data['monthly']);
new Chart(document.getElementById('revChart'), {
    type: 'line',
    data: {
        labels: rData.map(r => months[r.month-1]+' '+r.year),
        datasets: [{ label: 'Revenue', data: rData.map(r => r.total), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', fill: true, tension: 0.4, pointRadius: 4 }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{beginAtZero:true, ticks:{ callback: v => '₱'+(v>=1000?(v/1000).toFixed(0)+'K':v) }}, x:{grid:{display:false}} } }
});
</script>
@endpush
