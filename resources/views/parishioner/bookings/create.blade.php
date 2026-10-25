@extends('layouts.portal')

@section('title', 'Book a Service')

@push('styles')
<style>
/* ══════════════════════════════════════════════
   BOOKING CALENDAR  — improved UI
══════════════════════════════════════════════ */
.cal-wrap {
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(15,23,42,0.08);
}

/* ── Header ── */
.cal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem;
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
}
.cal-nav {
    width: 36px; height: 36px; border-radius: 10px;
    border: 1.5px solid rgba(255,255,255,0.25);
    background: rgba(255,255,255,0.12);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all 0.18s; color: #fff; flex-shrink: 0;
}
.cal-nav:hover {
    background: rgba(255,255,255,0.28);
    border-color: rgba(255,255,255,0.6);
    transform: scale(1.08);
}
.cal-nav svg { color: #fff; width: 16px; height: 16px; }
.cal-month {
    font-weight: 800; font-size: 1.0625rem; color: #fff;
    letter-spacing: 0.3px; text-align: center; flex: 1;
}

/* ── Day-of-week headers ── */
.cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); }
.cal-day-hdr {
    text-align: center; font-size: 0.65rem; font-weight: 700;
    color: #64748b; padding: 0.625rem 0;
    text-transform: uppercase; letter-spacing: 0.06em;
    background: #f8faff;
    border-bottom: 1.5px solid #e8edf5;
}

/* ── Day cells ── */
.cal-day {
    aspect-ratio: 1;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    font-size: 0.8125rem; font-weight: 600;
    cursor: pointer; border-radius: 10px; margin: 3px;
    transition: all 0.18s cubic-bezier(0.34,1.56,0.64,1);
    position: relative; color: #374151;
    min-height: 40px; user-select: none;
}
@media (min-width: 480px) {
    .cal-day { font-size: 0.875rem; margin: 3px; min-height: 46px; border-radius: 12px; }
    .cal-day-hdr { font-size: 0.7rem; padding: 0.75rem 0; }
}

.cal-day:hover:not(.past):not(.booked):not(.empty) {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(37,99,235,0.22);
    z-index: 1;
}

/* Today */
.cal-day.today {
    font-weight: 800; color: #1d4ed8;
    background: #dbeafe;
    box-shadow: inset 0 0 0 2px #93c5fd;
}
.cal-day.today::after {
    content: ''; position: absolute; bottom: 5px; left: 50%;
    transform: translateX(-50%); width: 4px; height: 4px;
    border-radius: 50%; background: #2563eb;
}

/* Selected */
.cal-day.selected {
    background: linear-gradient(135deg, #1e3a8a, #2563eb) !important;
    color: #fff !important;
    font-weight: 800;
    box-shadow: 0 6px 18px rgba(37,99,235,0.45) !important;
    transform: scale(1.12) !important;
    z-index: 2;
}
.cal-day.selected::after { display: none; }
.cal-day.selected::before {
    content: '✓'; position: absolute; top: 3px; right: 5px;
    font-size: 0.55rem; color: rgba(255,255,255,0.85);
    font-weight: 900;
}

/* Available */
.cal-day.available {
    background: #f0fdf4;
    color: #15803d;
}
.cal-day.available:hover {
    background: #bbf7d0 !important;
    color: #14532d !important;
}

/* Busy */
.cal-day.busy {
    background: #fefce8;
    color: #92400e;
    box-shadow: inset 0 0 0 1.5px #fde68a;
}
.cal-day.busy:hover { background: #fde68a !important; }
.cal-day.busy::after {
    content: '';
    position: absolute; bottom: 5px; left: 50%;
    transform: translateX(-50%);
    width: 18px; height: 3px;
    border-radius: 9999px;
    background: linear-gradient(to right, #f59e0b, #d97706);
}

/* Fully booked */
.cal-day.booked {
    background: #fff1f2;
    color: #be123c;
    cursor: not-allowed;
    box-shadow: inset 0 0 0 1.5px #fda4af;
    opacity: 0.8;
}
.cal-day.booked::after {
    content: '✕';
    position: absolute; bottom: 4px; left: 50%;
    transform: translateX(-50%);
    font-size: 0.55rem; color: #f43f5e; font-weight: 900;
}

/* Past */
.cal-day.past {
    color: #cbd5e1; cursor: not-allowed; background: transparent;
}
.cal-day.past:hover { background: transparent !important; transform: none !important; box-shadow: none !important; }
.cal-day.empty { cursor: default; }

/* ── Calendar body padding ── */
.cal-grid.px-2 { padding: 0 6px; }
.cal-grid.pb-2 { padding-bottom: 6px; }
.cal-grid.pt-2 { padding-top: 6px; }

/* ── Legend ── */
.cal-legend {
    padding: 0.875rem 1.125rem;
    border-top: 1.5px solid #e8edf5;
    background: linear-gradient(to bottom, #f8faff, #f1f5f9);
}
.cal-legend-title {
    font-size: 0.65rem; font-weight: 800; letter-spacing: 0.12em;
    text-transform: uppercase; color: #94a3b8;
    margin-bottom: 0.625rem;
}
.cal-legend-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem 1.25rem;
}
.leg-item {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.78rem; color: #475569;
}
.leg-swatch {
    width: 30px; height: 24px; border-radius: 8px;
    flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    font-size: 0.65rem; font-weight: 800;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}
.leg-desc { line-height: 1.3; }
.leg-desc strong { display: block; font-weight: 700; color: #1e293b; font-size: 0.8rem; }
.leg-desc span { color: #64748b; font-size: 0.7rem; }

/* ── Today jump button ── */
.cal-today-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 9999px;
    font-size: 0.72rem; font-weight: 700;
    background: rgba(255,255,255,0.18); color: #fff;
    border: 1px solid rgba(255,255,255,0.3);
    cursor: pointer; transition: all 0.15s;
}
.cal-today-btn:hover { background: rgba(255,255,255,0.32); }

/* ── Inline calendar warning ── */
.cal-warn-full {
    background: #fff1f2; border: 1.5px solid #fda4af; color: #9f1239;
}
.cal-warn-busy {
    background: #fefce8; border: 1.5px solid #fde68a; color: #78350f;
}
.cal-warn-ok {
    background: #f0fdf4; border: 1.5px solid #86efac; color: #14532d;
}

/* ── Shake animation for booked-date rejection feedback ── */
@keyframes cal-shake {
    0%, 100% { transform: translateX(0); }
    20%      { transform: translateX(-4px); }
    40%      { transform: translateX(4px); }
    60%      { transform: translateX(-3px); }
    80%      { transform: translateX(3px); }
}
.cal-day-shake {
    animation: cal-shake 0.45s ease-in-out;
}

/* ── Availability badge ── */
.avail-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 14px; border-radius: 9999px;
    font-size: 0.75rem; font-weight: 700;
}
.avail-ok   { background: #dcfce7; color: #14532d; }
.avail-busy { background: #fef9c3; color: #78350f; }
.avail-full { background: #ffe4e6; color: #9f1239; }

/* ── Selected date display ── */
.date-selected-card {
    background: linear-gradient(135deg, #eff6ff, #eef2ff);
    border: 1.5px solid #a5b4fc;
    border-radius: 0.875rem;
    padding: 0.875rem 1.125rem;
    display: flex; align-items: center; gap: 0.875rem;
    box-shadow: 0 2px 10px rgba(99,102,241,0.12);
}
.date-selected-icon {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(37,99,235,0.3);
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
                        @php
                            $elig      = $eligibilityResults[$service->slug] ?? null;
                            $isEligible = $elig ? $elig['eligible'] : true; // services without rules = open
                            $blockCount = $elig ? count(array_filter($elig['items'] ?? [], fn($i) => $i['required'] && !$i['satisfied'])) : 0;
                        @endphp
                        <label class="relative flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all
                                      hover:border-blue-300 hover:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50
                                      {{ !$isEligible ? 'opacity-75' : '' }}">
                            <input type="radio" name="booking_type" value="{{ $service->slug }}"
                                   class="mt-0.5 text-blue-600 focus:ring-blue-500 service-radio"
                                   data-service="{{ $service->slug }}" required
                                   {{ old('booking_type') === $service->slug || ($preService && $preService->slug === $service->slug) ? 'checked' : '' }}>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-sm text-gray-900">{{ $service->name }}</p>
                                @if($service->description)
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $service->description }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap gap-1.5 items-center">
                                    @if($service->fee > 0)
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                        ₱{{ number_format($service->fee, 0) }}
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        Free / Donation
                                    </span>
                                    @endif

                                    @if($elig)
                                        @if($isEligible)
                                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full">
                                            ✓ Eligible
                                        </span>
                                        @else
                                        <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-full">
                                            {{ $blockCount }} req. missing
                                        </span>
                                        @endif
                                    @endif
                                </div>

                                @if($elig && !$isEligible)
                                <p class="text-xs text-red-600 mt-1.5 font-medium">
                                    ⚠ You must meet eligibility requirements before booking.
                                    <a href="{{ route('parishioner.eligibility.index') }}" class="underline ml-1">View checklist →</a>
                                </p>
                                @endif
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

        {{-- ── ELIGIBILITY CHECKLIST PANEL ──────────────────────────────────
             Injected between Step 1 (service) and Step 2 (date).
             Loaded via JS when service changes; server also enforces in store().
        --}}
        <div id="eligibility-panel" class="hidden">
            <div class="bg-white rounded-2xl border-2 border-amber-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-amber-100 bg-amber-50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center text-sm font-bold shrink-0">!</div>
                    <div>
                        <h2 class="font-bold text-gray-900">Requirements Checklist</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Complete these before you can select a date and submit your booking.</p>
                    </div>
                </div>
                <div id="eligibility-items" class="px-6 py-4 space-y-3"></div>
                <div id="eligibility-progress-wrap" class="px-6 pb-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold text-gray-500">Progress</span>
                        <span class="text-xs font-bold text-blue-600" id="eligibility-pct">0 / 0 satisfied</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div id="eligibility-bar" class="h-2 rounded-full bg-blue-500 transition-all" style="width:0%"></div>
                    </div>
                </div>
                <div id="eligibility-blocked-msg" class="px-6 pb-5 hidden">
                    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-800 flex items-start gap-2">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <span>You must satisfy all required items before scheduling your booking. Visit <a href="{{ route('parishioner.my-requirements.index') }}" class="font-bold underline">My Requirements</a> to submit missing documents or declarations.</span>
                    </div>
                </div>
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
                            <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                                <span class="cal-month" id="cal-month-label"></span>
                                <button type="button" class="cal-today-btn" onclick="jumpToToday()">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Today
                                </button>
                            </div>
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

                    {{-- Inline availability warning — shown immediately below the calendar on date click --}}
                    <div id="cal-inline-warning" class="hidden mt-3 rounded-xl px-4 py-3 flex items-center gap-2.5 text-sm font-semibold">
                        <span id="cal-inline-icon" style="font-size:1rem;flex-shrink:0;"></span>
                        <span id="cal-inline-text"></span>
                    </div>

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
                        <label class="form-label">Service Location</label>
                        <select name="location_type" id="location_type_select" class="form-select w-full" onchange="toggleLocationFields(this.value)">
                            <option value="in_church" {{ old('location_type','in_church')==='in_church' ? 'selected' : '' }}>In-Church (Parish Premises)</option>
                            <option value="off_site"  {{ old('location_type')==='off_site'  ? 'selected' : '' }}>Off-Site (Home / Other Venue)</option>
                        </select>
                    </div>
                    {{-- Off-site fields — hidden unless off_site is selected --}}
                    <div id="offsite-fields" class="{{ old('location_type')==='off_site' ? '' : 'hidden' }} sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <label class="form-label">Off-Site Address <span class="text-red-500">*</span></label>
                            <input type="text" name="address" value="{{ old('address') }}"
                                   class="form-input w-full @error('address') border-red-400 @enderror"
                                   placeholder="House No., Street, Barangay, City — where service will be performed">
                            @error('address')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="form-label">Contact Person <span class="text-gray-400 text-xs">(at venue)</span></label>
                            <input type="text" name="contact_person" value="{{ old('contact_person') }}"
                                   class="form-input w-full" placeholder="Name of person at the location">
                        </div>
                        <div>
                            <label class="form-label">Contact Phone <span class="text-gray-400 text-xs">(at venue)</span></label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone') }}"
                                   class="form-input w-full" placeholder="09XX-XXX-XXXX">
                        </div>
                    </div>
                    {{-- In-church address (optional note) --}}
                    <div id="inchurch-fields" class="{{ old('location_type')==='off_site' ? 'hidden' : '' }}">
                        <label class="form-label">Additional Address Info <span class="text-gray-400 text-xs">(optional)</span></label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               class="form-input w-full"
                               placeholder="e.g., specific chapel or area within the parish">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Additional Notes <span class="text-gray-400 text-xs">(optional)</span></label>
                        <textarea name="notes" rows="3"
                                  class="form-input w-full @error('notes') border-red-400 @enderror"
                                  placeholder="Special requests, names of persons involved, etc.">{{ old('notes') }}</textarea>
                        @error('notes')<p class="form-error">{{ $message }}</p>@enderror
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
        checkEligibility(this.value);   // ← new
    });
});

// Pre-select if old value
const oldService = document.querySelector('.service-radio:checked');
if (oldService) {
    currentService = oldService.value;
    checkEligibility(currentService);   // ← new: run on page load too
}

// Always load availability on page load (shows all bookings even without service selected)
loadAvailability();

// ── Navigate months ─────────────────────────────────────────────────────────
function calNav(dir) {
    calMonth += dir;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    if (calMonth < 0)  { calMonth = 11; calYear--; }
    // Hide inline warning when navigating months — no date selected in new month yet
    const warn = document.getElementById('cal-inline-warning');
    if (warn) warn.classList.add('hidden');
    loadAvailability();
}

// ── Jump to today ────────────────────────────────────────────────────────────
function jumpToToday() {
    const now = new Date();
    calYear  = now.getFullYear();
    calMonth = now.getMonth();
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
        } else if (classes.includes('booked')) {
            // Booked cells: show the inline warning on click instead of silently ignoring it
            cell.addEventListener('click', () => showBookedWarning(cell));
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

    // ── Update inline warning directly below the calendar ────────────────────
    const warn     = document.getElementById('cal-inline-warning');
    const warnIcon = document.getElementById('cal-inline-icon');
    const warnText = document.getElementById('cal-inline-text');

    warn.classList.remove('hidden', 'cal-warn-full', 'cal-warn-busy', 'cal-warn-ok');

    if (bookedDates.includes(dateStr)) {
        warn.classList.add('cal-warn-full');
        warnIcon.textContent = '✕';
        warnText.textContent = 'Fully Booked — This date has reached its maximum booking capacity. Please choose another date.';
    } else if (busyDates.includes(dateStr)) {
        warn.classList.add('cal-warn-busy');
        warnIcon.textContent = '⚠';
        warnText.textContent = 'Slots Available — This date has existing bookings but is still open for scheduling.';
    } else {
        warn.classList.add('cal-warn-ok');
        warnIcon.textContent = '✓';
        warnText.textContent = 'Slots Available — This date is open with no existing bookings.';
    }

    // Update availability badge in Step 2 header (existing behaviour)
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

// ── Show warning when a fully-booked date is clicked ─────────────────────────
// Fires the inline banner and shakes the cell so the user knows why nothing happened.
function showBookedWarning(cell) {
    const warn     = document.getElementById('cal-inline-warning');
    const warnIcon = document.getElementById('cal-inline-icon');
    const warnText = document.getElementById('cal-inline-text');

    // Show the warning banner
    warn.classList.remove('hidden', 'cal-warn-full', 'cal-warn-busy', 'cal-warn-ok');
    warn.classList.add('cal-warn-full');
    warnIcon.textContent = '✕';
    warnText.textContent = 'Fully Booked – This date has reached its maximum booking capacity. Please select another date.';

    // Scroll the warning into view smoothly
    warn.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    // Shake animation on the clicked cell for tactile feedback
    cell.classList.add('cal-day-shake');
    setTimeout(() => cell.classList.remove('cal-day-shake'), 500);
}

// ── Format date as YYYY-MM-DD ─────────────────────────────────────────────────
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
        return;
    }
    // Disable submit button to prevent double-submission
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-not-allowed');
    btn.innerHTML = '<svg style="width:16px;height:16px;animation:spin 1s linear infinite;flex-shrink:0" fill="none" viewBox="0 0 24 24"><circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/></svg> Submitting…';
});

// ── Off-site / In-church location toggle ────────────────────────────────────
function toggleLocationFields(type) {
    const offsite  = document.getElementById('offsite-fields');
    const inchurch = document.getElementById('inchurch-fields');
    if (!offsite || !inchurch) return;
    if (type === 'off_site') {
        offsite.classList.remove('hidden');
        inchurch.classList.add('hidden');
    } else {
        offsite.classList.add('hidden');
        inchurch.classList.remove('hidden');
    }
}

// ── Eligibility checklist ────────────────────────────────────────────────────
let eligibilityEligible = true;

function checkEligibility(slug) {
    if (!slug) return;

    const panel     = document.getElementById('eligibility-panel');
    const itemsEl   = document.getElementById('eligibility-items');
    const pctEl     = document.getElementById('eligibility-pct');
    const barEl     = document.getElementById('eligibility-bar');
    const blockedEl = document.getElementById('eligibility-blocked-msg');
    const submitBtn = document.getElementById('submit-btn');
    const step2     = document.querySelector('[data-step2]');

    if (!panel || !itemsEl) return;

    fetch(`/portal/eligibility/check/${encodeURIComponent(slug)}`)
        .then(r => r.json())
        .then(data => {
            // If no items — service is open, hide panel
            if (!data.items || data.items.length === 0) {
                panel.classList.add('hidden');
                eligibilityEligible = true;
                enableSubmit(submitBtn);
                return;
            }

            panel.classList.remove('hidden');

            const stateColors = {
                satisfied : 'text-green-600',
                pending   : 'text-yellow-600',
                rejected  : 'text-red-600',
                missing   : 'text-gray-400',
            };
            const stateIcons = {
                satisfied : '✓',
                pending   : '⏳',
                rejected  : '✗',
                missing   : '○',
            };

            itemsEl.innerHTML = data.items.map(item => `
                <div class="flex items-start gap-2.5 py-2 border-b border-gray-50 last:border-0">
                    <span class="text-base shrink-0 ${stateColors[item.state] || 'text-gray-400'} font-bold w-5 text-center">
                        ${stateIcons[item.state] || '○'}
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-900">
                            ${item.name}
                            ${!item.is_required ? '<span class="text-gray-400 font-normal text-xs ml-1">(optional)</span>' : ''}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">${item.message}</p>
                        ${item.action_url && item.state !== 'satisfied' ? `
                        <a href="${item.action_url}"
                           class="inline-flex items-center gap-1 mt-1.5 text-xs font-bold text-blue-600 hover:underline">
                           ${item.action_label || 'Take Action'} →
                        </a>` : ''}
                    </div>
                </div>
            `).join('');

            // Progress bar
            const prog = data.progress || {};
            const pct  = prog.pct ?? 0;
            pctEl.textContent = `${prog.satisfied ?? 0} / ${prog.total ?? 0} satisfied`;
            barEl.style.width = pct + '%';
            barEl.className   = 'h-2 rounded-full transition-all ' + (pct === 100 ? 'bg-green-500' : 'bg-blue-500');

            eligibilityEligible = data.eligible;

            if (data.eligible) {
                blockedEl.classList.add('hidden');
                enableSubmit(submitBtn);
            } else {
                blockedEl.classList.remove('hidden');
                disableSubmit(submitBtn);
            }
        })
        .catch(() => {
            // On network error — fail open (don't block the UI)
            panel.classList.add('hidden');
            eligibilityEligible = true;
            enableSubmit(submitBtn);
        });
}

function disableSubmit(btn) {
    if (!btn) return;
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    btn.title = 'Complete all required eligibility items first.';
}

function enableSubmit(btn) {
    if (!btn) return;
    btn.disabled = false;
    btn.classList.remove('opacity-50', 'cursor-not-allowed');
    btn.title = '';
}
</script>
@push('styles')
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endpush
