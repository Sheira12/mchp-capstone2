@extends('layouts.app')

@section('title', 'Financial Report')
@section('page-title', 'Financial Report')

@section('content')
<div class="py-6 space-y-5">

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ $from }}" class="form-input text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ $to }}" class="form-input text-sm">
            </div>
            <button type="submit" class="btn-secondary text-sm">Apply</button>
        </form>
    </div>

    {{-- Summary --}}
    @php
        $totalRevenue = $data->sum('total');
        $totalCount   = $data->sum('count');
        $byMethod     = $data->groupBy('payment_method');
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <p class="text-3xl font-bold text-green-600 mt-1">₱{{ number_format($totalRevenue, 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $totalCount }} transactions</p>
        </div>
        @foreach($byMethod as $method => $rows)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500 capitalize">{{ $method }}</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">₱{{ number_format($rows->sum('total'), 2) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $rows->sum('count') }} transactions</p>
        </div>
        @endforeach
    </div>

    {{-- Chart --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Daily Revenue</h3>
        <canvas id="revenueChart" height="100"></canvas>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Method</th>
                    <th class="px-4 py-3 font-medium">Transactions</th>
                    <th class="px-4 py-3 font-medium">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($data as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-700">{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-gray-600 capitalize">{{ $row->payment_method }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $row->count }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-900">₱{{ number_format($row->total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">No data for selected period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const data = @json($data);
const dates = [...new Set(data.map(r => r.date))].sort();
const methods = [...new Set(data.map(r => r.payment_method))];
const colors = { cash: '#10b981', gcash: '#3b82f6', paymaya: '#8b5cf6' };

const datasets = methods.map(method => ({
    label: method.charAt(0).toUpperCase() + method.slice(1),
    data: dates.map(date => {
        const row = data.find(r => r.date === date && r.payment_method === method);
        return row ? row.total : 0;
    }),
    backgroundColor: colors[method] || '#6b7280',
    borderRadius: 4,
}));

new Chart(document.getElementById('revenueChart').getContext('2d'), {
    type: 'bar',
    data: { labels: dates, datasets },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } }
        }
    }
});
</script>
@endpush
