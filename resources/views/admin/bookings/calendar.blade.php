@extends('layouts.app')
@section('title', 'Booking Calendar')
@section('page-title', 'Booking Calendar')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="text-sm text-gray-500">Showing pending and confirmed bookings</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary text-sm">☰ List View</a>
            <a href="{{ route('admin.bookings.create') }}" class="btn-primary text-sm">+ New Booking</a>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-5 bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-3 flex-wrap">
        <span class="text-sm font-semibold text-gray-600">Legend:</span>
        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full inline-block bg-amber-500"></span><span class="text-sm text-gray-600">Pending</span></div>
        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full inline-block bg-green-600"></span><span class="text-sm text-gray-600">Confirmed</span></div>
    </div>

    {{-- Calendar Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Calendar nav --}}
        <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-blue-700 to-indigo-800 text-white">
            <button onclick="calPrev()" class="w-9 h-9 rounded-lg bg-white/15 hover:bg-white/25 flex items-center justify-center transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="text-center">
                <p class="font-bold text-lg" id="cal-month-label"></p>
                <p class="text-blue-200 text-xs">Booking Calendar</p>
            </div>
            <button onclick="calNext()" class="w-9 h-9 rounded-lg bg-white/15 hover:bg-white/25 flex items-center justify-center transition">
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
const allBookings = @json($bookings);
const today = new Date();
today.setHours(0,0,0,0);

let calYear  = {{ $year }};
let calMonth = {{ $month - 1 }}; // 0-indexed

const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

function renderCalendar() {
    document.getElementById('cal-month-label').textContent = monthNames[calMonth] + ' ' + calYear;

    const grid = document.getElementById('cal-grid');
    grid.innerHTML = '';

    const firstDay    = new Date(calYear, calMonth, 1).getDay();
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();

    // Group bookings by date
    const byDate = {};
    allBookings.forEach(b => {
        const d = b.start.split('T')[0].split(' ')[0];
        if (!byDate[d]) byDate[d] = [];
        byDate[d].push(b);
    });

    // Empty cells
    for (let i = 0; i < firstDay; i++) {
        const cell = document.createElement('div');
        cell.style.cssText = 'min-height:90px;border-right:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;background:#fafbff;';
        grid.appendChild(cell);
    }

    // Day cells
    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = calYear + '-' + String(calMonth+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
        const dayBookings = byDate[dateStr] || [];
        const isToday = new Date(calYear, calMonth, d).toDateString() === today.toDateString();

        const cell = document.createElement('div');
        cell.style.cssText = `min-height:90px;border-right:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;padding:4px;cursor:${dayBookings.length ? 'pointer' : 'default'};transition:background 0.15s;position:relative;background:${isToday ? '#eff6ff' : '#fff'};`;
        if (dayBookings.length) {
            cell.onmouseover = () => cell.style.background = '#f0f9ff';
            cell.onmouseout  = () => cell.style.background = isToday ? '#eff6ff' : '#fff';
        }

        // Day number
        const dayNum = document.createElement('div');
        dayNum.textContent = d;
        dayNum.style.cssText = `font-size:0.8125rem;font-weight:${isToday ? '800' : '500'};color:${isToday ? '#2563eb' : '#374151'};margin-bottom:3px;`;
        if (isToday) {
            dayNum.style.cssText += 'display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#2563eb;color:#fff;border-radius:50%;';
        }
        cell.appendChild(dayNum);

        // Show up to 3 booking pills
        const showMax = 3;
        dayBookings.slice(0, showMax).forEach(b => {
            const pill = document.createElement('div');
            const isConfirmed = b.color === '#16a34a';
            const parts = b.title.split(' - ');
            pill.textContent = parts[0];
            pill.style.cssText = `background:${isConfirmed ? '#d1fae5' : '#fef3c7'};color:${isConfirmed ? '#065f46' : '#92400e'};font-size:0.65rem;font-weight:600;padding:1px 5px;border-radius:4px;margin-bottom:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;`;
            pill.onclick = (e) => { e.stopPropagation(); showModal(b); };
            cell.appendChild(pill);
        });

        if (dayBookings.length > showMax) {
            const more = document.createElement('div');
            more.textContent = `+${dayBookings.length - showMax} more`;
            more.style.cssText = 'font-size:0.65rem;color:#2563eb;font-weight:600;cursor:pointer;';
            more.onclick = (e) => { e.stopPropagation(); showDayModal(dateStr, dayBookings); };
            cell.appendChild(more);
        }

        if (dayBookings.length) {
            cell.onclick = () => showDayModal(dateStr, dayBookings);
        }

        grid.appendChild(cell);
    }
}

function calPrev() {
    calMonth--;
    if (calMonth < 0) { calMonth = 11; calYear--; }
    renderCalendar();
}

function calNext() {
    calMonth++;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    renderCalendar();
}

function showModal(b) {
    const parts = b.title.split(' - ');
    const service = parts[0] || b.title;
    const name    = parts[1] || '';
    const isConf  = b.color === '#16a34a';
    const dateStr = (b.start || '').split('T')[0];
    const dateObj = dateStr ? new Date(dateStr + 'T00:00:00') : null;
    const dateFormatted = dateObj ? dateObj.toLocaleDateString('en-PH', {weekday:'long',year:'numeric',month:'long',day:'numeric'}) : '—';

    document.getElementById('modal-title').textContent = service;
    document.getElementById('modal-body').innerHTML = `
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:${b.color};"></span>
            <span class="font-semibold" style="color:${b.color};">${isConf ? 'Confirmed' : 'Pending Approval'}</span>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Parishioner</span>
                <span class="font-semibold text-gray-900">${name || '—'}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Service</span>
                <span class="font-semibold text-gray-900">${service}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Date</span>
                <span class="font-semibold text-gray-900">${dateFormatted}</span>
            </div>
        </div>
    `;
    document.getElementById('modal-link').href = b.url || '#';
    document.getElementById('booking-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('booking-modal').classList.add('hidden');
}

function showDayModal(dateStr, bookings) {
    const dateObj = new Date(dateStr + 'T00:00:00');
    const dateFormatted = dateObj.toLocaleDateString('en-PH', {weekday:'long', year:'numeric', month:'long', day:'numeric'});

    document.getElementById('day-modal-title').textContent = dateFormatted;

    const body = document.getElementById('day-modal-body');
    body.innerHTML = '';
    bookings.forEach(b => {
        const isConf = b.color === '#16a34a';
        const parts = b.title.split(' - ');
        const service = parts[0];
        const name = parts[1] || '—';

        const item = document.createElement('div');
        item.style.cssText = 'background:#f8faff;border:1px solid #e8edf5;border-radius:0.75rem;padding:0.875rem;';
        item.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <span style="font-weight:700;color:#0f172a;">${service}</span>
                <span style="background:${isConf ? '#d1fae5' : '#fef3c7'};color:${isConf ? '#065f46' : '#92400e'};font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:9999px;">${isConf ? 'Confirmed' : 'Pending'}</span>
            </div>
            <p style="font-size:0.8125rem;color:#64748b;margin-bottom:8px;">👤 ${name}</p>
            <a href="${b.url}" style="display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#fff;font-size:0.78rem;font-weight:600;padding:5px 12px;border-radius:6px;text-decoration:none;">
                View Details →
            </a>
        `;
        body.appendChild(item);
    });

    document.getElementById('day-modal').classList.remove('hidden');
}

function closeDayModal() {
    document.getElementById('day-modal').classList.add('hidden');
}

// Close modals on backdrop click
['booking-modal','day-modal'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });
});

// Init
renderCalendar();
</script>
@endpush
