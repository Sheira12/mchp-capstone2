@extends('layouts.app')
@section('title', 'Analytics & Data Analysis')
@section('page-title', 'Analytics & Data Analysis')

@push('styles')
<style>
/* ── Cards ── */
.an-card {
    background:#fff; border-radius:1rem; border:1px solid #e2e8f0;
    padding:1.25rem; box-shadow:0 1px 4px rgba(0,0,0,.04);
}
.an-card-title {
    font-size:0.72rem; font-weight:700; letter-spacing:.08em;
    text-transform:uppercase; color:#64748b; margin-bottom:.75rem;
}

/* ── Chart containers — FIXED HEIGHT so bars don't overflow ── */
.chart-wrap { position:relative; width:100%; height:260px; }
.chart-wrap-sm { position:relative; width:100%; height:220px; }
.chart-wrap-doughnut { position:relative; width:100%; height:240px; }

/* ── Heatmap ── */
.heatmap-scroll { overflow-x:auto; }
#heatmap-grid { display:grid; grid-template-columns:44px repeat(24,minmax(20px,1fr)); gap:2px; min-width:600px; }
.hm-label {
    font-size:0.6rem; color:#94a3b8;
    display:flex; align-items:center; justify-content:flex-end;
    padding-right:5px; height:20px;
}
.hm-cell {
    height:20px; border-radius:2px; background:#f1f5f9;
    cursor:default; position:relative;
}
.hm-cell:hover::after {
    content:attr(data-tip);
    position:absolute; bottom:calc(100%+4px); left:50%;
    transform:translateX(-50%);
    background:#1e293b; color:#fff; font-size:0.6rem;
    padding:2px 6px; border-radius:4px;
    white-space:nowrap; z-index:20; pointer-events:none;
}
.hm-hour-labels {
    display:grid; grid-template-columns:44px repeat(24,minmax(20px,1fr));
    gap:2px; min-width:600px; margin-bottom:3px;
}
.hm-hour-lbl { font-size:0.55rem; color:#cbd5e1; text-align:center; }

/* ── Insight cards ── */
.insight-card {
    background:linear-gradient(135deg,#f8faff,#f0f9ff);
    border:1.5px solid #bfdbfe; border-radius:.75rem;
    padding:.875rem 1rem; display:flex; gap:.75rem; align-items:flex-start;
}
.insight-icon { font-size:1.3rem; flex-shrink:0; line-height:1; }
.insight-title { font-size:.72rem; font-weight:700; color:#1e3a8a; margin-bottom:.15rem; }
.insight-text { font-size:.78rem; color:#475569; line-height:1.5; }

/* ── Section header ── */
.section-head {
    font-size:1rem; font-weight:700; color:#0f172a;
    padding-bottom:.5rem; border-bottom:2px solid #e2e8f0; margin-bottom:1rem;
}
</style>
@endpush

@section('content')
<div class="py-4 space-y-6 max-w-7xl">

    {{-- ── Page header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Analytics & Data Analysis</h1>
            <p class="text-sm text-gray-500">Insights from parish data — cached 5 min.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="loadData(true)"
                    class="inline-flex items-center gap-1.5 text-sm bg-white border border-gray-300 text-gray-700 font-semibold px-3 py-2 rounded-lg hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Refresh
            </button>
            <a href="{{ route('admin.analytics.export') }}"
               class="inline-flex items-center gap-1.5 text-sm bg-blue-600 text-white font-semibold px-3 py-2 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- ── Spinner ── --}}
    <div id="analytics-loading" class="py-20 text-center text-gray-400">
        <svg class="w-8 h-8 mx-auto mb-3 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <p class="text-sm font-medium">Loading analytics…</p>
    </div>

    <div id="analytics-content" class="hidden space-y-6">

        {{-- ── 1. KEY INSIGHTS ── --}}
        <div>
            <p class="section-head">💡 Key Insights</p>
            <div id="insights-grid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3"></div>
        </div>

        {{-- ── 2. BOOKING HEATMAP (full width) ── --}}
        <div class="an-card">
            <p class="an-card-title">📅 Booking Heatmap — Day × Hour (all bookings)</p>
            <p class="text-xs text-gray-400 mb-3">Darker = more bookings at that day and hour. Hover for count.</p>
            <div class="heatmap-scroll">
                <div id="heatmap-hour-labels" class="hm-hour-labels"></div>
                <div id="heatmap-grid"></div>
            </div>
            <div class="flex items-center gap-3 mt-3 text-xs text-gray-400">
                <div class="flex items-center gap-1"><div class="w-4 h-3 rounded" style="background:rgba(37,99,235,.06)"></div>Low</div>
                <div class="flex items-center gap-1"><div class="w-4 h-3 rounded" style="background:rgba(37,99,235,.4)"></div>Medium</div>
                <div class="flex items-center gap-1"><div class="w-4 h-3 rounded" style="background:rgba(37,99,235,.9)"></div>High</div>
            </div>
        </div>

        {{-- ── 3. MONTHLY + DEMAND (2 col) ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="an-card">
                <p class="an-card-title">📊 Monthly Bookings + 3-Month Forecast</p>
                <div class="chart-wrap"><canvas id="chart-monthly"></canvas></div>
            </div>
            <div class="an-card">
                <p class="an-card-title">🏆 Service Demand Ranking</p>
                <div class="chart-wrap"><canvas id="chart-demand"></canvas></div>
            </div>
        </div>

        {{-- ── 4. SACRAMENT TRENDS (full width) ── --}}
        <div class="an-card">
            <p class="an-card-title">✝ Sacrament Trends (last 24 months)</p>
            <div class="chart-wrap"><canvas id="chart-sacrament"></canvas></div>
        </div>

        {{-- ── 5. REVENUE + AGE (2 col) ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="an-card">
                <p class="an-card-title">💰 Revenue by Service</p>
                <div class="chart-wrap-doughnut"><canvas id="chart-revenue"></canvas></div>
            </div>
            <div class="an-card">
                <p class="an-card-title">👥 Parishioner Age Groups</p>
                <div class="chart-wrap-sm"><canvas id="chart-age"></canvas></div>
            </div>
        </div>

        {{-- ── 6. TOP BARANGAYS (full width) ── --}}
        <div class="an-card">
            <p class="an-card-title">📍 Top 10 Barangays by Parishioner Count</p>
            <div class="chart-wrap-sm"><canvas id="chart-brgy"></canvas></div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
'use strict';

const PALETTE = [
    '#3b82f6','#f59e0b','#10b981','#ef4444','#8b5cf6',
    '#06b6d4','#f97316','#14b8a6','#e11d48','#a855f7',
    '#84cc16','#0ea5e9','#ec4899','#6366f1','#22d3ee',
];

let _charts = {};

function killChart(id) {
    if (_charts[id]) { _charts[id].destroy(); delete _charts[id]; }
}

/* ── Shared Chart.js defaults ── */
Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#64748b';

function loadData(bust = false) {
    document.getElementById('analytics-loading').classList.remove('hidden');
    document.getElementById('analytics-content').classList.add('hidden');

    const url = '{{ route("admin.analytics.data") }}' + (bust ? '?bust=1' : '');

    fetch(url)
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(d => {
            renderInsights(d.insights  || []);
            renderHeatmap(d.heatmap    || {});
            renderMonthly(d.monthly_bookings || [], d.forecast || []);
            renderDemand(d.service_demand    || []);
            renderSacrament(d.sacrament_trends || {});
            renderRevenue(d.revenue_by_service || []);
            renderAge(d.demographics_age       || []);
            renderBarangay(d.demographics_brgy || []);

            document.getElementById('analytics-loading').classList.add('hidden');
            document.getElementById('analytics-content').classList.remove('hidden');
        })
        .catch(err => {
            document.getElementById('analytics-loading').innerHTML =
                '<p class="text-red-500 text-sm font-medium">Failed to load analytics. ' +
                '<button onclick="loadData()" class="underline text-blue-600">Retry</button> ' +
                '<span class="text-gray-400 ml-2 text-xs">(' + err.message + ')</span></p>';
            console.error('[analytics]', err);
        });
}

/* ── Insights ── */
function renderInsights(ins) {
    const el = document.getElementById('insights-grid');
    el.innerHTML = ins.length ? '' : '<p class="text-sm text-gray-400 col-span-3">No insights yet — add bookings and payments to see data.</p>';
    ins.forEach(i => {
        el.insertAdjacentHTML('beforeend',
            `<div class="insight-card"><div class="insight-icon">${i.icon}</div>
            <div><div class="insight-title">${i.title}</div>
            <div class="insight-text">${i.text}</div></div></div>`);
    });
}

/* ── Heatmap ── */
function renderHeatmap(hm) {
    const gridEl  = document.getElementById('heatmap-grid');
    const lblsEl  = document.getElementById('heatmap-hour-labels');
    const g       = hm.grid || {};
    const max     = Math.max(1, hm.max || 1);
    const days    = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const hours   = ['12a','1a','2a','3a','4a','5a','6a','7a','8a','9a','10a','11a',
                     '12p','1p','2p','3p','4p','5p','6p','7p','8p','9p','10p','11p'];

    // Hour labels row
    lblsEl.innerHTML = '<div></div>' + hours.map(h => `<div class="hm-hour-lbl">${h}</div>`).join('');

    gridEl.innerHTML = '';
    for (let d = 0; d < 7; d++) {
        gridEl.insertAdjacentHTML('beforeend', `<div class="hm-label">${days[d]}</div>`);
        for (let h = 0; h < 24; h++) {
            const cnt   = (g[d] && g[d][h]) ? g[d][h] : 0;
            const alpha = cnt === 0 ? 0.05 : Math.min(0.12 + (cnt / max) * 0.88, 1);
            const tip   = `${days[d]} ${hours[h]} — ${cnt} booking${cnt !== 1 ? 's' : ''}`;
            gridEl.insertAdjacentHTML('beforeend',
                `<div class="hm-cell" data-tip="${tip}" style="background:rgba(37,99,235,${alpha.toFixed(2)})"></div>`);
        }
    }
}

/* ── Monthly + Forecast ── */
function renderMonthly(monthly, forecast) {
    killChart('monthly');
    const hLabels = monthly.map(m => m.month);
    const hData   = monthly.map(m => m.count);

    // 3-month moving average
    const ma3 = hData.map((_, i) =>
        i < 2 ? null : Math.round((hData[i] + hData[i-1] + hData[i-2]) / 3));

    const fLabels = forecast.map(f => f.month);
    const fData   = forecast.map(f => f.forecast);

    const labels = [...hLabels, ...fLabels];
    const barD   = [...hData,   ...Array(fLabels.length).fill(null)];
    const maD    = [...ma3,     ...Array(fLabels.length).fill(null)];
    const fcD    = [...Array(hLabels.length).fill(null), ...fData];

    _charts['monthly'] = new Chart(document.getElementById('chart-monthly'), {
        data: {
            labels,
            datasets: [
                { type:'bar',  label:'Bookings',     data:barD, backgroundColor:'rgba(59,130,246,.5)', borderColor:'#3b82f6', borderWidth:1, borderRadius:3 },
                { type:'line', label:'3-Month Avg',  data:maD,  borderColor:'#f59e0b', borderWidth:2, pointRadius:0, tension:.4, fill:false },
                { type:'line', label:'Forecast',     data:fcD,  borderColor:'#10b981', borderWidth:2, borderDash:[6,4], pointRadius:4, tension:.4, fill:false, pointBackgroundColor:'#10b981' },
            ],
        },
        options: {
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, padding:12 } } },
            scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

/* ── Sacrament Trends ── */
function renderSacrament(data) {
    killChart('sacrament');
    if (!data.labels?.length) return;
    _charts['sacrament'] = new Chart(document.getElementById('chart-sacrament'), {
        type:'line',
        data:{
            labels:data.labels,
            datasets:(data.datasets || []).map((ds,i) => ({
                label:ds.label, data:ds.data,
                borderColor:PALETTE[i % PALETTE.length],
                backgroundColor:PALETTE[i % PALETTE.length] + '18',
                borderWidth:2, tension:.4, fill:false, pointRadius:2,
            })),
        },
        options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, padding:10 } } },
            scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

/* ── Service Demand ── */
function renderDemand(data) {
    killChart('demand');
    if (!data.length) return;
    _charts['demand'] = new Chart(document.getElementById('chart-demand'), {
        type:'bar',
        data:{
            labels:data.map(d => d.label),
            datasets:[{ label:'Total Bookings', data:data.map(d => d.total),
                backgroundColor:data.map((_,i) => PALETTE[i % PALETTE.length] + 'cc'), borderRadius:4 }],
        },
        options:{
            indexAxis:'y', responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

/* ── Revenue Doughnut ── */
function renderRevenue(data) {
    killChart('revenue');
    if (!data.length) return;
    _charts['revenue'] = new Chart(document.getElementById('chart-revenue'), {
        type:'doughnut',
        data:{
            labels:data.map(d => d.label),
            datasets:[{ data:data.map(d => d.total),
                backgroundColor:data.map((_,i) => PALETTE[i % PALETTE.length]), hoverOffset:6 }],
        },
        options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{
                legend:{ position:'right', labels:{ boxWidth:10, padding:10 } },
                tooltip:{ callbacks:{ label: ctx => ' ₱' + ctx.parsed.toLocaleString('en-PH',{minimumFractionDigits:2}) } },
            },
        },
    });
}

/* ── Age Groups ── */
function renderAge(data) {
    killChart('age');
    if (!data.length) return;
    _charts['age'] = new Chart(document.getElementById('chart-age'), {
        type:'bar',
        data:{
            labels:data.map(d => d.label),
            datasets:[{ label:'Parishioners', data:data.map(d => d.count),
                backgroundColor:PALETTE, borderRadius:4 }],
        },
        options:{
            responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

/* ── Top Barangays ── */
function renderBarangay(data) {
    killChart('brgy');
    if (!data.length) return;
    _charts['brgy'] = new Chart(document.getElementById('chart-brgy'), {
        type:'bar',
        data:{
            labels:data.map(d => d.barangay),
            datasets:[{ label:'Parishioners', data:data.map(d => d.count),
                backgroundColor:'#6366f1cc', borderRadius:4 }],
        },
        options:{
            indexAxis:'y', responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ display:false } },
            scales:{ x:{ beginAtZero:true, ticks:{ precision:0 } } },
        },
    });
}

document.addEventListener('DOMContentLoaded', () => loadData());
</script>
@endpush
