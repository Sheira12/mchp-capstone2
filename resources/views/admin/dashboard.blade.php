@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="py-6 space-y-6">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Parishioners</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($stats['totalParishioners']) }}</p>
                    <p class="text-xs text-green-600 mt-1">+{{ $stats['newParishioners'] }} this month</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending Bookings</p>
                    <p class="text-3xl font-bold text-amber-600 mt-1">{{ $stats['pendingBookings'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $stats['confirmedBookings'] }} confirmed</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Revenue (This Month)</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">₱{{ number_format($stats['totalRevenue'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Via e-wallet & cash</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending Certificates</p>
                    <p class="text-3xl font-bold text-purple-600 mt-1">{{ $stats['pendingCertificates'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">Awaiting release</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Sacraments Chart --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Sacraments This Month</h3>
                <span class="text-xs text-gray-400">{{ now()->format('F Y') }}</span>
            </div>
            {{-- Fixed height wrapper — prevents Chart.js from expanding infinitely --}}
            <div style="position:relative; height:260px;">
                <canvas id="sacramentsChart"></canvas>
            </div>
        </div>

        {{-- Revenue Chart --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Revenue Trend (12 Months)</h3>
                <span class="text-xs font-bold text-green-600">₱{{ number_format($stats['revenueTrend']->sum('total'), 0) }} total</span>
            </div>
            <div style="position:relative; height:260px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Sacrament Breakdown + Recent Bookings --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Sacrament Breakdown --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Sacrament Breakdown
                <span class="text-xs font-normal text-gray-400 ml-1">(this month)</span>
            </h3>
            @php
                $sacramentLabels = [
                    'baptism'         => ['label' => 'Baptism',         'color' => '#3b82f6'],
                    'first_communion' => ['label' => 'First Communion',  'color' => '#10b981'],
                    'confirmation'    => ['label' => 'Confirmation',     'color' => '#8b5cf6'],
                    'marriage'        => ['label' => 'Marriage',         'color' => '#ec4899'],
                    'death_burial'    => ['label' => 'Death/Burial',     'color' => '#6b7280'],
                ];
                $maxCount = max(array_values($stats['sacramentCounts']) + [1]);
                $totalSac = array_sum($stats['sacramentCounts']);
            @endphp
            @if($totalSac === 0)
            <p class="text-sm text-gray-400 text-center py-4">No sacramental records this month.</p>
            @else
            <div class="space-y-3">
                @foreach($sacramentLabels as $key => $info)
                @php $count = $stats['sacramentCounts'][$key] ?? 0; @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $info['color'] }}"></div>
                            <span class="text-sm text-gray-600">{{ $info['label'] }}</span>
                        </div>
                        <span class="text-sm font-bold text-gray-800">{{ $count }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-500"
                             style="width: {{ $count > 0 ? min(100, round(($count / $maxCount) * 100)) : 0 }}%; background: {{ $info['color'] }}">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between text-xs text-gray-400">
                <span>Total this month</span>
                <span class="font-bold text-gray-700">{{ $totalSac }}</span>
            </div>
            @endif
        </div>

        {{-- Recent Bookings --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Recent Bookings</h3>
                <a href="{{ route('admin.bookings.index') }}" class="text-sm text-blue-600 hover:underline">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-100">
                            <th class="pb-2 font-medium">Parishioner</th>
                            <th class="pb-2 font-medium">Service</th>
                            <th class="pb-2 font-medium">Scheduled</th>
                            <th class="pb-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($stats['recentBookings'] as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2.5">
                                @if($booking->parishioner)
                                <a href="{{ route('admin.bookings.show', $booking) }}"
                                   class="font-medium text-gray-900 hover:text-blue-700 leading-tight block">
                                    {{ $booking->parishioner->full_name }}
                                </a>
                                <span class="text-xs text-gray-400">Ref: {{ $booking->reference_number }}</span>
                                @else
                                <span class="text-gray-400 italic">Walk-in</span>
                                @endif
                            </td>
                            <td class="py-2.5 text-gray-600">{{ $booking->getTypeLabel() }}</td>
                            <td class="py-2.5 text-gray-500 whitespace-nowrap">
                                {{ $booking->scheduled_date->format('M d, Y') }}
                                @if($booking->scheduled_time)
                                <span class="text-xs text-gray-400 block">{{ \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') }}</span>
                                @endif
                            </td>
                            <td class="py-2.5">
                                @php
                                    $sc = ['pending'=>'amber','confirmed'=>'green','completed'=>'blue','cancelled'=>'red'][$booking->status] ?? 'gray';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-800">
                                    {{ $booking->getStatusLabel() }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-6 text-center text-gray-400 text-sm">No bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Descriptive Statistics Panel ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Descriptive Statistics</h3>
            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">Frequency · Percentage · Median</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Sacrament Frequency & Percentage --}}
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Sacrament Frequency (This Month)</p>
                @php
                    $sacLabels = ['baptism'=>'Baptism','first_communion'=>'First Communion','confirmation'=>'Confirmation','marriage'=>'Marriage','death_burial'=>'Death/Burial'];
                    $sacColors = ['baptism'=>'#3b82f6','first_communion'=>'#10b981','confirmation'=>'#8b5cf6','marriage'=>'#ec4899','death_burial'=>'#6b7280'];
                    $totalSacStat = array_sum(array_column($stats['sacramentStats'] ?? [], 'count'));
                @endphp
                @if(empty($stats['sacramentStats']) || $totalSacStat === 0)
                    <p class="text-sm text-gray-400">No data this month.</p>
                @else
                <div class="space-y-2">
                    @foreach($sacLabels as $key => $label)
                    @php $s = $stats['sacramentStats'][$key] ?? ['count'=>0,'percentage'=>0]; @endphp
                    <div class="flex items-center gap-2 text-xs">
                        <div class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $sacColors[$key] }}"></div>
                        <span class="text-gray-600 flex-1">{{ $label }}</span>
                        <span class="font-bold text-gray-800 w-6 text-right">{{ $s['count'] }}</span>
                        <span class="text-gray-400 w-12 text-right">{{ $s['percentage'] }}%</span>
                    </div>
                    @endforeach
                    <div class="pt-2 border-t border-gray-100 flex justify-between text-xs font-bold text-gray-700">
                        <span>Total</span><span>{{ $totalSacStat }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Booking Type Frequency --}}
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Booking Type Frequency (This Month)</p>
                @php $totalBT = array_sum(array_column($stats['bookingTypeStats'] ?? [], 'count')); @endphp
                @if(empty($stats['bookingTypeStats']) || $totalBT === 0)
                    <p class="text-sm text-gray-400">No bookings this month.</p>
                @else
                <div class="space-y-2">
                    @foreach($stats['bookingTypeStats'] as $type => $bt)
                    <div class="flex items-center gap-2 text-xs">
                        <div class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></div>
                        <span class="text-gray-600 flex-1 capitalize">{{ str_replace('_',' ', $type) }}</span>
                        <span class="font-bold text-gray-800 w-6 text-right">{{ $bt['count'] }}</span>
                        <span class="text-gray-400 w-12 text-right">{{ $bt['percentage'] }}%</span>
                    </div>
                    @endforeach
                    <div class="pt-2 border-t border-gray-100 flex justify-between text-xs font-bold text-gray-700">
                        <span>Total</span><span>{{ $totalBT }}</span>
                    </div>
                </div>
                @endif
            </div>

            {{-- Median & Averages --}}
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Key Metrics (Last 12 Months)</p>
                <div class="space-y-3">
                    <div class="bg-blue-50 rounded-xl p-3">
                        <p class="text-xs text-blue-500 font-semibold uppercase tracking-wide">Median Payment</p>
                        <p class="text-xl font-bold text-blue-700 mt-0.5">₱{{ number_format($stats['medianPayment'] ?? 0, 2) }}</p>
                        <p class="text-xs text-blue-400">Middle value of all payments</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <p class="text-xs text-green-500 font-semibold uppercase tracking-wide">Avg. Monthly Bookings</p>
                        <p class="text-xl font-bold text-green-700 mt-0.5">{{ $stats['avgMonthlyBookings'] ?? 0 }}</p>
                        <p class="text-xs text-green-400">Average per month</p>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3">
                        <p class="text-xs text-purple-500 font-semibold uppercase tracking-wide">Total Parishioners</p>
                        <p class="text-xl font-bold text-purple-700 mt-0.5">{{ number_format($stats['totalParishioners']) }}</p>
                        <p class="text-xs text-purple-400">+{{ $stats['newParishioners'] }} registered this month</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.bookings.qr-scanner') }}"
           class="flex flex-col items-center gap-2 bg-white border border-gray-100 rounded-xl p-4 hover:bg-blue-50 hover:border-blue-200 transition shadow-sm group">
            <div class="w-10 h-10 rounded-xl bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center transition">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <span class="text-xs font-semibold text-gray-700 text-center">QR Scanner</span>
        </a>
        <a href="{{ route('admin.bookings.create') }}"
           class="flex flex-col items-center gap-2 bg-white border border-gray-100 rounded-xl p-4 hover:bg-green-50 hover:border-green-200 transition shadow-sm group">
            <div class="w-10 h-10 rounded-xl bg-green-100 group-hover:bg-green-200 flex items-center justify-center transition">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="text-xs font-semibold text-gray-700 text-center">New Booking</span>
        </a>
        <a href="{{ route('admin.certificates.create') }}"
           class="flex flex-col items-center gap-2 bg-white border border-gray-100 rounded-xl p-4 hover:bg-purple-50 hover:border-purple-200 transition shadow-sm group">
            <div class="w-10 h-10 rounded-xl bg-purple-100 group-hover:bg-purple-200 flex items-center justify-center transition">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-xs font-semibold text-gray-700 text-center">Issue Certificate</span>
        </a>
        <a href="{{ route('admin.parishioners.create') }}"
           class="flex flex-col items-center gap-2 bg-white border border-gray-100 rounded-xl p-4 hover:bg-amber-50 hover:border-amber-200 transition shadow-sm group">
            <div class="w-10 h-10 rounded-xl bg-amber-100 group-hover:bg-amber-200 flex items-center justify-center transition">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <span class="text-xs font-semibold text-gray-700 text-center">Add Parishioner</span>
        </a>
    </div>

    {{-- Generate Report --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Generate Report</h3>        <form action="{{ route('admin.dashboard.export') }}" method="POST" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Period</label>
                <select name="period" class="form-select text-sm" onchange="toggleCustomDates(this.value)">
                    <option value="month">This Month</option>
                    <option value="week">This Week</option>
                    <option value="year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div id="custom-dates" class="hidden flex gap-2">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">From</label>
                    <input type="date" name="date_from" class="form-input text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">To</label>
                    <input type="date" name="date_to" class="form-input text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Format</label>
                <select name="type" class="form-select text-sm">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm">Generate Report</button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Sacraments Doughnut Chart
const sacramentsCtx = document.getElementById('sacramentsChart').getContext('2d');

@php
$baptism       = $stats['sacramentCounts']['baptism']        ?? 0;
$firstCom      = $stats['sacramentCounts']['first_communion'] ?? 0;
$confirmation  = $stats['sacramentCounts']['confirmation']    ?? 0;
$marriage      = $stats['sacramentCounts']['marriage']        ?? 0;
$deathBurial   = $stats['sacramentCounts']['death_burial']    ?? 0;
$totalSacraments = $baptism + $firstCom + $confirmation + $marriage + $deathBurial;
@endphp

const sacData = [{{ $baptism }}, {{ $firstCom }}, {{ $confirmation }}, {{ $marriage }}, {{ $deathBurial }}];
const sacTotal = sacData.reduce((a, b) => a + b, 0);

new Chart(sacramentsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Baptism', 'First Communion', 'Confirmation', 'Marriage', 'Death/Burial'],
        datasets: [{
            data: sacData,
            backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#6b7280'],
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 16,
                    usePointStyle: true,
                    pointStyleWidth: 10,
                    font: { size: 12 },
                }
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        const val = ctx.parsed;
                        const pct = sacTotal > 0 ? ((val / sacTotal) * 100).toFixed(1) : 0;
                        return ' ' + ctx.label + ': ' + val + ' (' + pct + '%)';
                    }
                }
            }
        }
    }
});

// Revenue Line Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueTrend = @json($stats['revenueTrend']);
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueTrend.map(r => months[r.month - 1] + ' ' + r.year),
        datasets: [{
            label: 'Revenue (₱)',
            data: revenueTrend.map(r => r.total),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.08)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3b82f6',
            pointRadius: 4,
            pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ₱' + ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: { callback: v => '₱' + (v >= 1000 ? (v/1000).toFixed(0) + 'K' : v) }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

function toggleCustomDates(val) {
    document.getElementById('custom-dates').classList.toggle('hidden', val !== 'custom');
}
</script>
@endpush
