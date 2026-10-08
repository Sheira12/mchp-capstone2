@extends('layouts.app')
@section('title', 'Analytics & Data Analysis')
@section('page-title', 'Analytics & Data Analysis')

@push('styles')
<style>
/* ── Section card ── */
.an-card { background:#fff; border-radius:1rem; border:1px solid #e2e8f0; padding:1.5rem; box-shadow:0 1px 4px rgba(0,0,0,.04); }
.an-card-title { font-size:0.75rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#64748b; margin-bottom:1rem; }

/* ── Heatmap grid ── */
#heatmap-grid { display:grid; grid-template-columns:60px repeat(24,1fr); gap:2px; }
.hm-label { font-size:0.65rem; color:#94a3b8; display:flex; align-items:center; justify-content:flex-end; padding-right:6px; }
.hm-cell {
    aspect-ratio:1; border-radius:3px; background:#f1f5f9;
    transition:transform .1s;
    cursor:default;
    position:relative;
}
.hm-cell:hover .hm-tooltip { display:block; }
.hm-tooltip {
    display:none; position:absolute; bottom:calc(100%+4px); left:50%; transform:translateX(-50%);
    background:#1e293b; color:#fff; font-size:0.65rem; padding:3px 7px; border-radius:5px;
    white-space:nowrap; z-index:10; pointer-events:none;
}
.hm-hour-labels { grid-column:1 / -1; display:grid; grid-template-columns:60px repeat(24,1fr); gap:2px; }
.hm-hour-lbl { font-size:0.6rem; color:#cbd5e1; text-align:center; }

/* ── Insight cards ── */
.insight-card { background:linear-gradient(135deg,#f8faff,#f0f9ff); border:1.5px solid #bfdbfe; border-radius:.75rem; padding:1rem 1.25rem; display:flex; gap:.75rem; align-items:flex-start; }
.insight-icon { font-size:1.5rem; flex-shrink:0; line-height:1; }
.insight-title { font-size:.75rem; font-weight:700; color:#1e3a8a; margin-bottom:.2rem; }
.insight-text  { font-size:.82rem; color:#475569; line-height:1.5; }

/* ── Stat pill ── */
.stat-pill { background:#f8fafc; border:1px solid #e2e8f0; border-radius:.5rem; padding:.6rem 1rem; text-align:center; }
.stat-pill .val { font-size:1.5rem; font-weight:800; color:#1e3a8a; line-height:1; }
.stat-pill .lbl { font-size:.68rem; font-weight:600; color:#64748b; margin-top:.2rem; text-transform:uppercase; letter-spacing:.06em; }
</style>
@endpush

@section('content')
<div class="py-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Analytics & Data Analysis</h1>
            <p class="text-sm text-gray-500 mt-0.5">Real-time insights from parish data. Cached for 5 minutes.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="loadData(true)" class="btn-secondary text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
            <a href="{{ route('admin.analytics.export') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Loading state --}}
    <div id="analytics-loading" class="py-20 text-center text-gray-400">
        <svg class="w-8 h-8 mx-auto mb-3 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <p class="text-sm">Loading analytics data…</p>
    </div>

    <div id="analytics-content" class="hidden space-y-6">

        {{-- ── PLAIN-LANGUAGE INSIGHTS ── --}}
        <div>
            <h2 class="text-lg font-bold text-gray-900 mb-3">Key Insights</h2>
            <div id="insights-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"></div>
        </div>

        {{-- ── ROW: Booking Heatmap ── --}}
        <div class="an-card">
            <p class="an-card-title">Booking Heatmap — Day × Time (all-time)</p>
            <p class="text-xs text-gray-400 mb-3">Each cell = bookings on that weekday at that hour. Darker = busier.</p>
            <div id="heatmap-hour-labels" class="hm-hour-labels mb-1"></div>
            <div id="heatmap-grid"></div>
            <div class="mt-2 flex items-center gap-2 text-xs text-gray-400">
                <div class="w-4 h-4 rounded bg-blue-50 border border-blue-100"></div> Low
                <div class="w-4 h-4 rounded bg-blue-200 ml-2"></div> Medium
                <div class="w-4 h-4 rounded bg-blue-600 ml-2"></div> High
            </div>
        </div>

        {{-- ── ROW: Monthly Trends + Forecast ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="an-card">
                <p class="an-card-title">Monthly Bookings (last 15 months + 3-month forecast)</p>
                <canvas id="chart-monthly" height="220"></canvas>
            </div>
            <div class="an-card">
                <p class="an-card-title">Sacrament Trends (last 12 months)</p>
                <canvas id="chart-sacrament" height="220"></canvas>
            </div>
        </div>

        {{-- ── ROW: Service Demand + Revenue ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="an-card">
                <p class="an-card-title">Service Demand Ranking</p>
                <canvas id="chart-demand" height="250"></canvas>
            </div>
            <div class="an-card">
                <p class="an-card-title">Revenue by Service Type</p>
                <canvas id="chart-revenue" height="250"></canvas>
            </div>
        </div>

        {{-- ── ROW: Demographics ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="an-card">
                <p class="an-card-title">Parishioner Age Groups</p>
                <canvas id="chart-age" height="220"></canvas>
            </div>
            <div class="an-card">
                <p class="an-card-title">Top 10 Barangays</p>
                <canvas id="chart-brgy" height="220"></canvas>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const COLORS = [
    '#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6',
    '#06b6d4','#f97316','#14b8a6','#e11d48','#a855f7',
    '#84cc16','#0ea5e9',
];

let charts = {};

function destroyChart(id) {
    if (charts[id]) { charts[id].destroy(); delete charts[id]; }
}

function loadData(bust = false) {
    document.getElementById('analytics-loading').classList.remove('hidden');
    document.getElementById('analytics-content').classList.add('hidden');

    const url = '{{ route("admin.analytics.data") }}' + (bust ? '?bust=1' : '');

    fetch(url)
        .then(r => r.json())
        .then(data => {
            renderInsights(data.insights || []);
            renderHeatmap(data.heatmap || {});
            renderMonthly(data.monthly_bookings || [], data.forecast || []);
            renderSacrament(data.sacrament_trends || {});
            renderDemand(data.service_demand || []);
            renderRevenue(data.revenue_by_service || []);
            renderAge(data.demographics_age || []);
            renderBarangay(data.demographics_brgy || []);

            document.getElementById('analytics-loading').classList.add('hidden');
            document.getElementById('analytics-content').classList.remove('hidden');
        })
        .catch(err => {
            document.getElementById('analytics-loading').innerHTML =
                '<p class="text-red-500 text-sm">Failed to load analytics data. <button onclick="loadData()" class="underline">Retry</button></p>';
            console.error(err);
        });
}

// ── Insights ──────────────────────────────────────────────────────────────
function renderInsights(insights) {
    const grid = document.getElementById('insights-grid');
    grid.innerHTML = '';
    insights.forEach(ins => {
        grid.innerHTML += `
            <div class="insight-card">
                <div class="insight-icon">${ins.icon}</div>
                <div>
                    <div class="insight-title">${ins.title}</div>
                    <div class="insight-text">${ins.text}</div>
                </div>
            </div>`;
    });
}

// ── Heatmap ───────────────────────────────────────────────────────────────
function renderHeatmap(heatmap) {
    const grid  = document.getElementById('heatmap-grid');
    const lbls  = document.getElementById('heatmap-hour-labels');
    const g     = heatmap.grid || {};
    const max   = heatmap.max  || 1;
    const days  = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    // Hour labels row
    lbls.innerHTML = '<div></div>' + Array.from({length:24}, (_,h) =>
        `<div class="hm-hour-lbl">${h === 0 ? '12a' : h < 12 ? h+'a' : h === 12 ? '12p' : (h-12)+'p'}</div>`
    ).join('');

    grid.innerHTML = '';
    for (let d = 0; d < 7; d++) {
        grid.innerHTML += `<div class="hm-label text-xs">${days[d]}</div>`;
        for (let h = 0; h < 24; h++) {
            const cnt = (g[d] && g[d][h]) ? g[d][h] : 0;
            const pct = Math.min(cnt / max, 1);
            const alpha = pct === 0 ? 0.05 : 0.1 + pct * 0.9;
            grid.innerHTML += `
                <div class="hm-cell" style="background:rgba(37,99,235,${alpha.toFixed(2)})">
                    <div class="hm-tooltip">${days[d]} ${h}:00 — ${cnt} booking${cnt !== 1 ? 's' : ''}</div>
                </div>`;
        }
    }
}

// ── Monthly + Forecast ────────────────────────────────────────────────────
function renderMonthly(monthly, forecast) {
    destroyChart('monthly');
    const historicLabels = monthly.map(m => m.month);
    const historicData   = monthly.map(m => m.count);

    // 3-month moving average overlay
    const ma3 = historicData.map((_, i) => {
        if (i < 2) return null;
        return Math.round((historicData[i] + historicData[i-1] + historicData[i-2]) / 3);
    });

    const forecastLabels = forecast.map(f => f.month);
    const forecastData   = forecast.map(f => f.forecast);

    const allLabels = [...historicLabels, ...forecastLabels];
    const barData   = [...historicData,   ...Array(forecastLabels.length).fill(null)];
    const maData    = [...ma3,            ...Array(forecastLabels.length).fill(null)];
    const fData     = [...Array(historicLabels.length).fill(null), ...forecastData];

    charts['monthly'] = new Chart(document.getElementById('chart-monthly'), {
        data: {
            labels: allLabels,
            datasets: [
                {
                    type: 'bar', label: 'Bookings',
                    data: barData, backgroundColor: 'rgba(59,130,246,.55)',
                    borderColor: '#3b82f6', borderWidth: 1, borderRadius: 4,
                },
                {
                    type: 'line', label: '3-Month Avg',
                    data: maData, borderColor: '#f59e0b', borderWidth: 2,
                    pointRadius: 0, tension: 0.4, fill: false,
                },
                {
                    type: 'line', label: 'Forecast',
                    data: fData, borderColor: '#10b981', borderWidth: 2,
                    borderDash: [6,4], pointRadius: 4, tension: 0.4, fill: false,
                    pointBackgroundColor: '#10b981',
                },
            ],
        },
        options: { responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ position:'bottom', labels:{ boxWidth:12, font:{size:11} } } },
            scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

// ── Sacrament Trends ──────────────────────────────────────────────────────
function renderSacrament(data) {
    destroyChart('sacrament');
    if (!data.labels || !data.datasets) return;

    charts['sacrament'] = new Chart(document.getElementById('chart-sacrament'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: data.datasets.map((ds, i) => ({
                label: ds.label, data: ds.data,
                borderColor: COLORS[i % COLORS.length],
                backgroundColor: COLORS[i % COLORS.length] + '22',
                borderWidth: 2, tension: 0.4, fill: false, pointRadius: 3,
            })),
        },
        options: { responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:10} } } },
            scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

// ── Service Demand ────────────────────────────────────────────────────────
function renderDemand(data) {
    destroyChart('demand');
    charts['demand'] = new Chart(document.getElementById('chart-demand'), {
        type: 'bar',
        data: {
            labels: data.map(d => d.label),
            datasets: [{
                label: 'Total Bookings',
                data: data.map(d => d.total),
                backgroundColor: data.map((_, i) => COLORS[i % COLORS.length] + 'cc'),
                borderRadius: 5,
            }],
        },
        options: {
            indexAxis: 'y', responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

// ── Revenue by Service ────────────────────────────────────────────────────
function renderRevenue(data) {
    destroyChart('revenue');
    if (!data.length) return;
    charts['revenue'] = new Chart(document.getElementById('chart-revenue'), {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.label),
            datasets: [{
                data: data.map(d => d.total),
                backgroundColor: data.map((_, i) => COLORS[i % COLORS.length]),
                hoverOffset: 8,
            }],
        },
        options: { responsive:true, maintainAspectRatio:false,
            plugins:{
                legend:{ position:'right', labels:{ boxWidth:12, font:{size:11} } },
                tooltip:{ callbacks:{ label: ctx => ` ₱${ctx.parsed.toLocaleString('en-PH',{minimumFractionDigits:2})}` } },
            },
        },
    });
}

// ── Age Demographics ──────────────────────────────────────────────────────
function renderAge(data) {
    destroyChart('age');
    charts['age'] = new Chart(document.getElementById('chart-age'), {
        type: 'bar',
        data: {
            labels: data.map(d => d.label),
            datasets: [{
                label: 'Parishioners',
                data: data.map(d => d.count),
                backgroundColor: COLORS,
                borderRadius: 5,
            }],
        },
        options: { responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

// ── Barangay Demographics ─────────────────────────────────────────────────
function renderBarangay(data) {
    destroyChart('brgy');
    charts['brgy'] = new Chart(document.getElementById('chart-brgy'), {
        type: 'bar',
        data: {
            labels: data.map(d => d.barangay),
            datasets: [{
                label: 'Parishioners',
                data: data.map(d => d.count),
                backgroundColor: '#6366f1cc',
                borderRadius: 5,
            }],
        },
        options: {
            indexAxis: 'y', responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

// Auto-load on page ready
document.addEventListener('DOMContentLoaded', () => loadData());
</script>
@endpush
