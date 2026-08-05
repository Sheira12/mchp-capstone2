@extends('layouts.portal')

@section('title', 'Book a Service')

@push('styles')
<style>
/* ── Calendar availability picker ── */
.cal-wrap {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.cal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.75rem 1rem;
    background: linear-gradient(to right, #1e3a8a, #2563eb);
    color: #fff;
}
.cal-nav {
    width: 32px; height: 32px; border-radius: 8px;
    border: 1.5px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.1);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all 0.15s; color: #fff;
}
.cal-nav:hover { background: rgba(255,255,255,0.25); border-color: rgba(255,255,255,0.5); }
.cal-nav svg { color: #fff; }
.cal-month { font-weight: 800; font-size: 0.9375rem; color: #fff; letter-spacing: 0.5px; }

.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
.cal-day-hdr {
    text-align: center; font-size: 0.6rem; font-weight: 700;
    color: #94a3b8; padding: 0.5rem 0;
    text-transform: uppercase; letter-spacing: 0.04em;
    background: #f8faff;
    border-bottom: 1px solid #f1f5f9;
}

.cal-day {
    aspect-ratio: 1;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 500;
    cursor: pointer; border-radius: 6px; margin: 1px;
    transition: all 0.15s; position: relative;
    color: #374151;
    min-height: 32px;
}
@media (min-width: 480px) {
    .cal-day { font-size: 0.8125rem; margin: 2px; min-height: 38px; border-radius: 8px; }
    .cal-day-hdr { font-size: 0.68rem; padding: 0.625rem 0; }
}
.cal-day:hover:not(.past):not(.booked):not(.empty) {
    background: #eff6ff; color: #2563eb;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(37,99,235,0.2);
}

/* Today */
.cal-day.today { font-weight: 800; color: #2563eb; background: #eff6ff; }
.cal-day.today::after {
    content: ''; position: absolute; bottom: 3px; left: 50%;
    transform: translateX(-50%); width: 3px; height: 3px;
    border-radius: 50%; background: #2563eb;
}

/* Selected */
.cal-day.selected {
    background: #2563eb !important; color: #fff !important;
    font-weight: 800; box-shadow: 0 4px 12px rgba(37,99,235,0.4);
    transform: scale(1.08);
}
.cal-day.selected::after { display: none; }
.cal-day.selected::before {
    content: '✓'; position: absolute; top: 2px; right: 3px;
    font-size: 0.5rem; color: rgba(255,255,255,0.8);
}

/* Available */
.cal-day.available {
    background: #f0fdf4;
    color: #166534;
}
.cal-day.available:hover {
    background: #dcfce7 !important;
    color: #14532d !important;
}

/* Busy — has 1-4 bookings */
.cal-day.busy {
    background: #fef9c3;
    color: #854d0e;
    border: 1.5px solid #fde047;
}
.cal-day.busy:hover { background: #fef08a !important; }
.cal-day.busy::after {
    content: '';
    position: absolute; bottom: 4px; left: 50%;
    transform: translateX(-50%);
    width: 16px; height: 3px;
    border-radius: 9999px;
    background: #eab308;
}

/* Fully booked */
.cal-day.booked {
    background: #fef2f2;
    color: #b91c1c;
    cursor: not-allowed;
    border: 1.5px solid #fca5a5;
    opacity: 0.85;
}
.cal-day.booked::after {
    content: '✕';
    position: absolute; bottom: 3px; left: 50%;
    transform: translateX(-50%);
    font-size: 0.55rem; color: #ef4444;
}

/* Past dates */
.cal-day.past {
    color: #d1d5db;
    cursor: not-allowed;
    background: transparent;
}
.cal-day.past:hover { background: transparent !important; transform: none !important; box-shadow: none !important; }

.cal-day.empty { cursor: default; }

/* ── Legend ── */
.cal-legend {
    padding: 0.75rem 1rem;
    border-top: 1px solid #f1f5f9;
    background: #fafbff;
}
.cal-legend-title {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em;
    text-transform: uppercase; color: #94a3b8;
    margin-bottom: 0.5rem;
}
.cal-legend-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.375rem 1rem;
}
.leg-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.78rem; color: #475569;
}
.leg-swatch {
    width: 28px; height: 22px; border-radius: 6px;
    flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; font-weight: 700;
}
.leg-desc { line-height: 1.3; }
.leg-desc strong { display: block; font-weight: 700; color: #1e293b; font-size: 0.8rem; }
.leg-desc span { color: #64748b; font-size: 0.72rem; }

/* ── Availability badge ── */
.avail-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; border-radius: 9999px;
    font-size: 0.75rem; font-weight: 700;
}
.avail-ok   { background: #d1fae5; color: #065f46; }
.avail-busy { background: #fef9c3; color: #854d0e; }
.avail-full { background: #fee2e2; color: #991b1b; }

/* ── Selected date display ── */
.date-selected-card {
    background: linear-gradient(135deg, #eff6ff, #f0f9ff);
    border: 1.5px solid #bfdbfe;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    display: flex; align-items: center; gap: 0.75rem;
}
.date-selected-icon {
    width: 36px; height: 36px; border-radius: 50%;
    background: #2563eb; color: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-3xl w-full">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.bookings.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Book a Service</h1>
            <p class="text-sm text-gray-500">Submit a booking request to the parish office</p>
        </div>
    </div>

    <form action="{{ route('parishioner.bookings.store') }}" method="POST" id="booking-form" class="space-y-6">
        @csrf

        {{-- Step 1: Service Selection --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">1</div>
                    <h2 class="font-bold text-gray-900">Select a Service</h2>
                </div>
            </div>
            <div class="p-6">
                @if($services->isEmpty())
                <p class="text-gray-400 text-sm">No bookable services available at this time.</p>
                @else
                @foreach($services as $category => $categoryServices)
                <div class="mb-6 last:mb-0">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ $category }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($categoryServices as $service)
                        <label class="relative flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-blue-300 hover:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="booking_type" value="{{ $service->slug }}"
                                   class="mt-0.5 text-blue-600 focus:ring-blue-500 service-radio"
                                   data-service="{{ $service->slug }}" required
                                   {{ old('booking_type') === $service->slug ? 'checked' : '' }}>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-sm text-gray-900">{{ $service->name }}</p>
                                @if($service->description)
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $service->description }}</p>
                                @endif
                                <div class="mt-2">
                                    @if($service->fee > 0)
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                        ₱{{ number_format($service->fee, 0) }}
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        Free / Donation
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @endif
                @error('booking_type')<p class="form-error mt-2">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Step 2: Schedule with Calendar Availability --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">2</div>
                        <h2 class="font-bold text-gray-900">Preferred Schedule</h2>
                    </div>
                    <div id="avail-badge" class="hidden"></div>
                </div>
            </div>
            <div class="p-6 space-y-5">

                {{-- Calendar availability picker --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="form-label mb-0">Select Date <span class="text-red-500">*</span></label>
                        <span class="text-xs text-gray-400" id="cal-hint">Select a service first to check availability</span>
                    </div>

                    <div class="cal-wrap">
                        {{-- Calendar header --}}
                        <div class="cal-header">
                            <button type="button" class="cal-nav" id="cal-prev" onclick="calNav(-1)">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <span class="cal-month" id="cal-month-label"></span>
                            <button type="button" class="cal-nav" id="cal-next" onclick="calNav(1)">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        {{-- Day headers --}}
                        <div class="cal-grid px-2 pt-2">
                            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                            <div class="cal-day-hdr">{{ $d }}</div>
                            @endforeach
                        </div>

                        {{-- Calendar days --}}
                        <div class="cal-grid px-2 pb-2" id="cal-days"></div>

                        {{-- Legend --}}
                        <div class="cal-legend">
                            <div class="cal-legend-title">Date Availability Guide</div>
                            <div class="cal-legend-grid">
                                <div class="leg-item">
                                    <div class="leg-swatch" style="background:#f0fdf4;border:1.5px solid #86efac;color:#16a34a;">✓</div>
                                    <div class="leg-desc">
                                        <strong>Available</strong>
                                        <span>No bookings — open for scheduling</span>
                                    </div>
                                </div>
                                <div class="leg-item">
                                    <div class="leg-swatch" style="background:#fef9c3;border:1.5px solid #fde047;color:#854d0e;">~</div>
                                    <div class="leg-desc">
                                        <strong>Has Bookings</strong>
                                        <span>Some slots taken — still bookable</span>
                                    </div>
                                </div>
                                <div class="leg-item">
                                    <div class="leg-swatch" style="background:#fef2f2;border:1.5px solid #fca5a5;color:#b91c1c;">✕</div>
                                    <div class="leg-desc">
                                        <strong>Fully Booked</strong>
                                        <span>No more slots — choose another date</span>
                                    </div>
                                </div>
                                <div class="leg-item">
                                    <div class="leg-swatch" style="background:#f1f5f9;color:#94a3b8;">—</div>
                                    <div class="leg-desc">
                                        <strong>Unavailable</strong>
                                        <span>Past date — cannot be selected</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden date input --}}
                    <input type="hidden" name="scheduled_date" id="scheduled_date" value="{{ old('scheduled_date') }}" required>

                    {{-- Selected date card --}}
                    <div id="selected-date-display" class="date-selected-card hidden mt-3">
                        <div class="date-selected-icon">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide">Selected Date</p>
                            <p class="font-bold text-blue-900 text-sm" id="selected-date-text"></p>
                        </div>
                    </div>
                    @error('scheduled_date')<p class="form-error mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Time + Address + Notes --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Preferred Time <span class="text-gray-400 text-xs">(optional)</span></label>
                        <input type="time" name="scheduled_time" value="{{ old('scheduled_time') }}" class="form-input w-full">
                        @error('scheduled_time')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Address <span class="text-gray-400 text-xs">(for blessings)</span></label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               class="form-input w-full" placeholder="Where service will be performed">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Additional Notes <span class="text-gray-400 text-xs">(optional)</span></label>
                        <textarea name="notes" rows="3" class="form-input w-full"
                                  placeholder="Special requests, names of persons involved, etc.">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notice --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-amber-800">
                <p class="font-bold mb-1">Important Reminders</p>
                <ul class="space-y-1 list-disc list-inside text-amber-700">
                    <li>Your booking is subject to approval by the parish office.</li>
                    <li>You will receive an email confirmation once approved.</li>
                    <li>Please ensure you have completed required seminars before booking sacraments.</li>
                    <li>Payment can be made via GCash, Maya, or cash at the parish office.</li>
                </ul>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex gap-3">
            <button type="submit" id="submit-btn"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-8 py-3 rounded-xl hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Submit Booking Request
            </button>
            <a href="{{ route('parishioner.bookings.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ── Calendar state ──────────────────────────────────────────────────────────
const today     = new Date();
today.setHours(0,0,0,0);
// Allow booking from today onwards (not just tomorrow)
const minDate   = new Date(today);

let calYear     = today.getFullYear();
let calMonth    = today.getMonth(); // 0-indexed
let selectedDate = null;
let bookedDates  = [];
let busyDates    = [];
let currentService = null;
let loadingCal   = false;

const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

// ── Restore old value if validation failed ──────────────────────────────────
const oldDate = document.getElementById('scheduled_date').value;
if (oldDate) {
    const d = new Date(oldDate + 'T00:00:00');
    selectedDate = d;
    calYear  = d.getFullYear();
    calMonth = d.getMonth();

    // Show the selected date card immediately
    const display  = document.getElementById('selected-date-display');
    const dateText = document.getElementById('selected-date-text');
    if (display && dateText) {
        dateText.textContent = d.toLocaleDateString('en-PH', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
        display.classList.remove('hidden');
    }
}

// ── Service radio change ────────────────────────────────────────────────────
document.querySelectorAll('.service-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        currentService = this.value;
        document.getElementById('cal-hint').textContent = 'Loading availability...';
        loadAvailability();
    });
});

// Pre-select if old value
const oldService = document.querySelector('.service-radio:checked');
if (oldService) {
    currentService = oldService.value;
}

// Always load availability on page load (shows all bookings even without service selected)
loadAvailability();

// ── Navigate months ─────────────────────────────────────────────────────────
function calNav(dir) {
    calMonth += dir;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    if (calMonth < 0)  { calMonth = 11; calYear--; }
    loadAvailability();
}

// ── Load availability from API ───────────────────────────────────────────────
function loadAvailability() {
    if (loadingCal) return;
    loadingCal = true;

    const monthStr = calYear + '-' + String(calMonth + 1).padStart(2, '0');
    const url = '/api/booked-dates?month=' + monthStr + (currentService ? '&type=' + currentService : '');

    fetch(url)
        .then(r => r.json())
        .then(data => {
            bookedDates = data.booked || [];
            busyDates   = data.busy   || [];
            loadingCal  = false;
            renderCalendar();

            // Update hint text
            const hint = document.getElementById('cal-hint');
            if (currentService) {
                hint.textContent = 'Green = available · Yellow = has bookings · Red = fully booked';
            } else {
                const total = bookedDates.length + busyDates.length;
                hint.textContent = total > 0
                    ? 'Showing all bookings · Select a service for specific availability'
                    : 'Select a service to check availability';
            }
        })
        .catch(() => {
            loadingCal = false;
            bookedDates = [];
            busyDates   = [];
            renderCalendar();
            document.getElementById('cal-hint').textContent = 'Could not load availability. All dates shown as available.';
        });
}

// ── Render calendar ──────────────────────────────────────────────────────────
function renderCalendar() {
    document.getElementById('cal-month-label').textContent = monthNames[calMonth] + ' ' + calYear;

    const grid    = document.getElementById('cal-days');
    grid.innerHTML = '';

    const firstDay    = new Date(calYear, calMonth, 1).getDay();
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();

    // Empty cells before first day
    for (let i = 0; i < firstDay; i++) {
        const empty = document.createElement('div');
        empty.className = 'cal-day empty';
        grid.appendChild(empty);
    }

    // Day cells
    for (let d = 1; d <= daysInMonth; d++) {
        const date    = new Date(calYear, calMonth, d);
        const dateStr = formatDate(date);
        const cell    = document.createElement('div');
        const isToday = date.toDateString() === today.toDateString();

        let classes = ['cal-day'];
        let tooltip = '';

        if (date < minDate) {
            classes.push('past');
            tooltip = 'Past date — not available';
        } else if (bookedDates.includes(dateStr)) {
            classes.push('booked');
            tooltip = 'Fully booked — please choose another date';
        } else {
            if (busyDates.includes(dateStr)) {
                classes.push('busy');
                tooltip = 'Has existing bookings — still available';
            } else {
                classes.push('available');
                tooltip = 'Available — click to select';
            }
            if (isToday) classes.push('today');
            if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
                classes.push('selected');
                tooltip = 'Selected date';
            }
        }

        cell.className = classes.join(' ');
        cell.textContent = d;
        cell.title = tooltip; // native browser tooltip

        if (!classes.includes('past') && !classes.includes('booked')) {
            cell.addEventListener('click', () => selectDate(date, dateStr));
        }

        grid.appendChild(cell);
    }
}

// ── Select a date ────────────────────────────────────────────────────────────
function selectDate(date, dateStr) {
    selectedDate = date;
    document.getElementById('scheduled_date').value = dateStr;

    // Show selected date card
    const display  = document.getElementById('selected-date-display');
    const dateText = document.getElementById('selected-date-text');
    dateText.textContent = date.toLocaleDateString('en-PH', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });
    display.classList.remove('hidden');

    // Update availability badge in header
    const badge = document.getElementById('avail-badge');
    badge.classList.remove('hidden');
    if (bookedDates.includes(dateStr)) {
        badge.innerHTML = '<span class="avail-badge avail-full">✕ Fully Booked</span>';
    } else if (busyDates.includes(dateStr)) {
        badge.innerHTML = '<span class="avail-badge avail-busy">⚠ Has Existing Bookings</span>';
    } else {
        badge.innerHTML = '<span class="avail-badge avail-ok">✓ Date Available</span>';
    }

    renderCalendar();
}

// ── Format date as YYYY-MM-DD ────────────────────────────────────────────────
function formatDate(date) {
    return date.getFullYear() + '-'
        + String(date.getMonth() + 1).padStart(2, '0') + '-'
        + String(date.getDate()).padStart(2, '0');
}

// ── Prevent submit if no date selected ──────────────────────────────────────
document.getElementById('booking-form').addEventListener('submit', function(e) {
    if (!document.getElementById('scheduled_date').value) {
        e.preventDefault();
        alert('Please select a date from the calendar.');
    }
});
</script>
@endpush
