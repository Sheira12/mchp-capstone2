@extends('layouts.app')
@section('title', 'Booking Calendar')
@section('page-title', 'Booking Calendar')

@push('styles')
<style>
/* ── Calendar cell ───────────────────────────────────── */
.cal-cell {
    min-height: 100px;
    border-right: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    padding: 5px;
    position: relative;
    transition: background 0.15s;
    vertical-align: top;
}
.cal-cell.is-past   { background: #fafafa; }
.cal-cell.is-today  { background: #eff6ff; }
.cal-cell.is-empty  { background: #fafbff; }
.cal-cell.clickable { cursor: pointer; }
.cal-cell.clickable:hover { background: #f0f9ff; }

/* Day number */
.cal-day-num {
    font-size: 0.8125rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 3px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
}
.cal-day-num.today-circle {
    background: #2563eb;
    color: #fff;
    font-weight: 800;
}
.cal-day-num.past-num { color: #cbd5e1; }

/* Count badge */
.cal-count-badge {
    position: absolute;
    top: 5px; right: 5px;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 9999px;
    line-height: 1.4;
}
.cal-count-pending   { background: #fef3c7; color: #92400e; }
.cal-count-confirmed { background: #d1fae5; color: #065f46; }
.cal-count-mixed     { background: #dbeafe; color: #1e40af; }

/* Booking pill */
.cal-pill {
    font-size: 0.65rem;
    font-weight: 600;
    padding: 2px 5px;
    border-radius: 4px;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
    display: block;
    transition: filter 0.1s;
}
.cal-pill:hover { filter: brightness(0.9); }
.cal-pill-pending   { background: #fef3c7; color: #92400e; }
.cal-pill-confirmed { background: #d1fae5; color: #065f46; }

/* Loading overlay */
#cal-loading {
    position: absolute; inset: 0;
    background: rgba(255,255,255,0.7);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 0 0 0.75rem 0.75rem;
}

/* Tooltip */
.cal-tooltip {
    position: absolute;
    z-index: 100;
    background: #1e293b;
    color: #f8fafc;
    font-size: 0.72rem;
    padding: 6px 10px;
    border-radius: 8px;
    white-space: nowrap;
    pointer-events: none;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
    max-width: 220px;
    white-space: normal;
    line-height: 1.5;
}
.cal-tooltip::before {
    content: '';
    position: absolute;
    top: -5px; left: 12px;
    border-width: 0 5px 5px;
    border-style: solid;
    border-color: transparent transparent #1e293b;
}
</style>
@endpush

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <p class="text-sm text-gray-500">Showing pending and confirmed bookings</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary text-sm">☰ List View</a>
            <a href="{{ route('admin.bookings.create') }}" class="btn-primary text-sm">+ New Booking</a>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-5 bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-3 flex-wrap">
        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Legend:</span>
        <div class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-full bg-amber-400"></span>
            <span class="text-sm text-gray-600">Pending</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-full bg-green-600"></span>
            <span class="text-sm text-gray-600">Confirmed</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">3</span>
            <span class="text-sm text-gray-600">Count badge</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-full bg-gray-200"></span>
            <span class="text-sm text-gray-400">Past date</span>
        </div>
    </div>

    {{-- Calendar Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" style="position:relative;">

        {{-- Loading overlay --}}
        <div id="cal-loading">
            <svg class="w-8 h-8 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 60" opacity=".3"/>
                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" opacity=".8"/>
            </svg>
        </div>

        {{-- Month nav header --}}
        <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-blue-700 to-indigo-800 text-white">
            <button onclick="calPrev()" id="btn-prev"
                    class="w-9 h-9 rounded-lg bg-white/15 hover:bg-white/25 flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="text-center">
                <p class="font-bold text-lg" id="cal-month-label"></p>
                <p class="text-blue-200 text-xs">Booking Calendar</p>
            </div>
            <button onclick="calNext()" id="btn-next"
                    class="w-9 h-9 rounded-lg bg-white/15 hover:bg-white/25 flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Day headers --}}
        <div style="display:grid;grid-template-columns:repeat(7,1fr);background:#f8faff;border-bottom:1px solid #e8edf5;">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
            <div style="text-align:center;padding:0.625rem 0;font-size:0.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;">{{ $d }}</div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div id="cal-grid" style="display:grid;grid-template-columns:repeat(7,1fr);"></div>

    </div>

    {{-- Tooltip (shared, moved by JS) --}}
    <div id="cal-tooltip" class="cal-tooltip hidden"></div>

    {{-- Booking Detail Modal --}}
    <div id="booking-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-gray-900 text-lg" id="modal-title">Booking Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="modal-body" class="space-y-3 text-sm"></div>
            <div class="mt-5 flex gap-3">
                <a id="modal-link" href="#" class="btn-primary text-sm flex-1 text-center">View Full Details</a>
                <button onclick="closeModal()" class="btn-secondary text-sm flex-1">Close</button>
            </div>
        </div>
    </div>

    {{-- Day Bookings Modal --}}
    <div id="day-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-gray-900 text-lg" id="day-modal-title">Bookings</h3>
                <button onclick="closeDayModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="day-modal-body" class="space-y-3"></div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ── State ──────────────────────────────────────────────────────────────────
let allBookings = @json($bookings);
let calYear     = {{ $year }};
let calMonth    = {{ $month - 1 }}; // 0-indexed

const today = new Date();
today.setHours(0, 0, 0, 0);

const CALENDAR_URL = '{{ route("admin.bookings.calendar") }}';
const monthNames   = ['January','February','March','April','May','June','July','August','September','October','November','December'];

// ── Render ─────────────────────────────────────────────────────────────────
function renderCalendar() {
    document.getElementById('cal-month-label').textContent = monthNames[calMonth] + ' ' + calYear;

    const grid        = document.getElementById('cal-grid');
    grid.innerHTML    = '';

    const firstDay    = new Date(calYear, calMonth, 1).getDay();
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();

    // Group bookings by date
    const byDate = {};
    allBookings.forEach(b => {
        const d = b.start.split('T')[0].split(' ')[0];
        if (!byDate[d]) byDate[d] = [];
        byDate[d].push(b);
    });

    // Empty cells before month start
    for (let i = 0; i < firstDay; i++) {
        const cell = document.createElement('div');
        cell.className = 'cal-cell is-empty';
        grid.appendChild(cell);
    }

    // Day cells
    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr   = calYear + '-' + String(calMonth + 1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
        const cellDate  = new Date(calYear, calMonth, d);
        const isPast    = cellDate < today;
        const isToday   = cellDate.toDateString() === today.toDateString();
        const dayBks    = byDate[dateStr] || [];
        const hasAny    = dayBks.length > 0;

        const cell = document.createElement('div');
        cell.className = 'cal-cell' +
            (isPast  ? ' is-past'  : '') +
            (isToday ? ' is-today' : '') +
            (hasAny && !isPast ? ' clickable' : '');

        // Gray-out past days completely
        if (isPast && !hasAny) {
            cell.style.opacity = '0.55';
        }

        if (hasAny && !isPast) {
            cell.onclick = () => showDayModal(dateStr, dayBks);
        }

        // Day number
        const dayNum = document.createElement('div');
        dayNum.className = 'cal-day-num' +
            (isToday ? ' today-circle' : '') +
            (isPast  ? ' past-num'     : '');
        dayNum.textContent = d;
        cell.appendChild(dayNum);

        // Count badge (top-right corner)
        if (hasAny) {
            const pending   = dayBks.filter(b => b.color !== '#16a34a').length;
            const confirmed = dayBks.filter(b => b.color === '#16a34a').length;
            const badge     = document.createElement('span');
            badge.className = 'cal-count-badge ' +
                (pending > 0 && confirmed > 0 ? 'cal-count-mixed' :
                 confirmed > 0               ? 'cal-count-confirmed' : 'cal-count-pending');
            badge.textContent = dayBks.length;

            // Tooltip on badge hover
            const tooltipLines = [];
            if (pending   > 0) tooltipLines.push('🟡 ' + pending   + ' pending');
            if (confirmed > 0) tooltipLines.push('🟢 ' + confirmed + ' confirmed');
            badge.addEventListener('mouseenter', e => showTooltip(e, tooltipLines.join('\n')));
            badge.addEventListener('mouseleave', hideTooltip);
            badge.addEventListener('click', e => { e.stopPropagation(); showDayModal(dateStr, dayBks); });
            cell.appendChild(badge);
        }

        // Booking pills (max 2 shown)
        const showMax = 2;
        dayBks.slice(0, showMax).forEach(b => {
            const isConf = b.color === '#16a34a';
            const pill   = document.createElement('div');
            pill.className = 'cal-pill ' + (isConf ? 'cal-pill-confirmed' : 'cal-pill-pending');
            pill.textContent = b.title.split(' - ')[0]; // service label only
            pill.title = b.title; // native fallback tooltip

            pill.addEventListener('mouseenter', e => showTooltip(e, b.title + '\n' + (isConf ? '✓ Confirmed' : '⏳ Pending')));
            pill.addEventListener('mouseleave', hideTooltip);
            pill.onclick = e => { e.stopPropagation(); showSingleModal(b); };
            cell.appendChild(pill);
        });

        if (dayBks.length > showMax) {
            const more = document.createElement('div');
            more.style.cssText = 'font-size:0.65rem;color:#2563eb;font-weight:600;cursor:pointer;padding:1px 0;';
            more.textContent = '+' + (dayBks.length - showMax) + ' more';
            more.onclick = e => { e.stopPropagation(); showDayModal(dateStr, dayBks); };
            cell.appendChild(more);
        }

        grid.appendChild(cell);
    }
}

// ── AJAX month navigation ──────────────────────────────────────────────────
async function loadMonth(year, month) {
    const loading = document.getElementById('cal-loading');
    loading.style.display = 'flex';
    document.getElementById('btn-prev').disabled = true;
    document.getElementById('btn-next').disabled = true;

    try {
        const res  = await fetch(`${CALENDAR_URL}?year=${year}&month=${month + 1}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
        });
        const data = await res.json();
        allBookings = data.bookings;
        renderCalendar();
    } catch (e) {
        console.error('Calendar load error:', e);
    } finally {
        loading.style.display = 'none';
        document.getElementById('btn-prev').disabled = false;
        document.getElementById('btn-next').disabled = false;
    }
}

function calPrev() {
    calMonth--;
    if (calMonth < 0) { calMonth = 11; calYear--; }
    loadMonth(calYear, calMonth);
}

function calNext() {
    calMonth++;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    loadMonth(calYear, calMonth);
}

// ── Tooltip ────────────────────────────────────────────────────────────────
const tooltip = document.getElementById('cal-tooltip');

function showTooltip(e, text) {
    tooltip.innerHTML = text.replace(/\n/g, '<br>');
    tooltip.classList.remove('hidden');
    positionTooltip(e);
}

function positionTooltip(e) {
    const rect = e.target.getBoundingClientRect();
    const scrollY = window.scrollY;
    tooltip.style.left = Math.max(0, rect.left) + 'px';
    tooltip.style.top  = (rect.bottom + scrollY + 6) + 'px';
}

function hideTooltip() {
    tooltip.classList.add('hidden');
}

// ── Single booking modal ───────────────────────────────────────────────────
function showSingleModal(b) {
    const parts   = b.title.split(' - ');
    const service = parts[0] || b.title;
    const name    = parts[1] || '';
    const isConf  = b.color === '#16a34a';
    const dateStr = (b.start || '').split('T')[0];
    const dateObj = dateStr ? new Date(dateStr + 'T00:00:00') : null;
    const dateFmt = dateObj ? dateObj.toLocaleDateString('en-PH', {weekday:'long',year:'numeric',month:'long',day:'numeric'}) : '—';

    document.getElementById('modal-title').textContent = service;
    document.getElementById('modal-body').innerHTML = `
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full" style="background:${b.color};flex-shrink:0;"></span>
            <span class="font-semibold" style="color:${b.color};">${isConf ? 'Confirmed' : 'Pending Approval'}</span>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 space-y-2">
            <div class="flex justify-between text-sm"><span class="text-gray-500">Parishioner</span><span class="font-semibold text-gray-900">${name || '—'}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Service</span><span class="font-semibold text-gray-900">${service}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Date</span><span class="font-semibold text-gray-900">${dateFmt}</span></div>
        </div>`;
    document.getElementById('modal-link').href = b.url || '#';
    document.getElementById('booking-modal').classList.remove('hidden');
}

function closeModal() { document.getElementById('booking-modal').classList.add('hidden'); }

// ── Day bookings modal ─────────────────────────────────────────────────────
function showDayModal(dateStr, bookings) {
    const dateObj  = new Date(dateStr + 'T00:00:00');
    const dateFmt  = dateObj.toLocaleDateString('en-PH', {weekday:'long', year:'numeric', month:'long', day:'numeric'});

    document.getElementById('day-modal-title').textContent = dateFmt;

    const body = document.getElementById('day-modal-body');
    body.innerHTML = '';

    bookings.forEach(b => {
        const isConf  = b.color === '#16a34a';
        const parts   = b.title.split(' - ');
        const service = parts[0];
        const name    = parts[1] || '—';

        const item = document.createElement('div');
        item.style.cssText = 'background:#f8faff;border:1px solid #e8edf5;border-radius:0.75rem;padding:0.875rem;';
        item.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <span style="font-weight:700;color:#0f172a;font-size:0.9rem;">${service}</span>
                <span style="background:${isConf ? '#d1fae5' : '#fef3c7'};color:${isConf ? '#065f46' : '#92400e'};font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:9999px;white-space:nowrap;">${isConf ? '✓ Confirmed' : '⏳ Pending'}</span>
            </div>
            <p style="font-size:0.8125rem;color:#64748b;margin-bottom:8px;">👤 ${name}</p>
            <a href="${b.url}" style="display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#fff;font-size:0.78rem;font-weight:600;padding:5px 12px;border-radius:6px;text-decoration:none;">
                View Details →
            </a>`;
        body.appendChild(item);
    });

    document.getElementById('day-modal').classList.remove('hidden');
}

function closeDayModal() { document.getElementById('day-modal').classList.add('hidden'); }

// Close modals on backdrop click
['booking-modal','day-modal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
});

// ── Init ───────────────────────────────────────────────────────────────────
renderCalendar();
</script>
@endpush
