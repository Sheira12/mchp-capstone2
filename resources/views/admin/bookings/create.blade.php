@extends('layouts.app')

@section('title', 'New Booking')
@section('page-title', 'New Booking')

@push('styles')
<style>
.search-results {
    position: absolute; z-index: 50; background: #fff;
    border: 1px solid #e2e8f0; border-radius: 0.75rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    width: 100%; max-height: 260px; overflow-y: auto; margin-top: 4px;
}
.search-result-item { padding: 0.625rem 1rem; cursor: pointer; border-bottom: 1px solid #f8faff; transition: background 0.15s; }
.search-result-item:last-child { border-bottom: none; }
.search-result-item:hover { background: #eff6ff; }
.search-result-item .name { font-weight: 600; font-size: 0.875rem; color: #0f172a; }
.search-result-item .meta { font-size: 0.75rem; color: #94a3b8; margin-top: 1px; }
</style>
@endpush

@section('content')
<div class="py-6 max-w-3xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.bookings.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">New Booking</h1>
            <p class="text-sm text-gray-500">Create a booking on behalf of a parishioner</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.bookings.store') }}" class="space-y-5">
        @csrf

        {{-- Parishioner Search --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center font-bold">1</span>
                Select Parishioner
            </h2>

            <div class="relative" id="p-search-wrap">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="p-search-input" placeholder="Type parishioner name to search…"
                           class="form-input w-full pl-9" autocomplete="off">
                </div>
                <div id="p-results" class="search-results hidden"></div>
            </div>

            <div id="p-selected" class="hidden mt-3 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <p id="p-selected-name" class="font-bold text-blue-900 text-sm"></p>
                    <p id="p-selected-meta" class="text-xs text-blue-500 mt-0.5"></p>
                </div>
                <button type="button" onclick="clearParishioner()" class="text-blue-400 hover:text-red-500 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <input type="hidden" name="parishioner_id" id="parishioner_id" value="{{ old('parishioner_id') }}" required>
            @error('parishioner_id')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Service & Schedule --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center font-bold">2</span>
                Service & Schedule
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="form-label">Service Type <span class="text-red-500">*</span></label>
                    <select name="booking_type" required class="form-select w-full @error('booking_type') border-red-400 @enderror"
                            onchange="updateFee(this)">
                        <option value="">Select service…</option>
                        @foreach(\App\Models\Booking::TYPES as $val => $label)
                        <option value="{{ $val }}" @selected(old('booking_type') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('booking_type')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Scheduled Date <span class="text-red-500">*</span></label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date') }}"
                           min="{{ now()->toDateString() }}" required
                           class="form-input w-full @error('scheduled_date') border-red-400 @enderror">
                    @error('scheduled_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Scheduled Time <span class="text-gray-400 text-xs">(optional)</span></label>
                    <input type="time" name="scheduled_time" value="{{ old('scheduled_time') }}" class="form-input w-full">
                    @error('scheduled_time')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Service Fee (₱)</label>
                    <input type="number" name="service_fee" id="service_fee" value="{{ old('service_fee', 0) }}"
                           step="0.01" min="0" class="form-input w-full">
                </div>

                <div>
                    <label class="form-label">Address <span class="text-gray-400 text-xs">(for blessings)</span></label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="form-input w-full" placeholder="Service location">
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" rows="3" class="form-input w-full"
                              placeholder="Additional notes or special requests…">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-7 py-2.5 rounded-xl hover:bg-blue-700 shadow-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Create Booking
            </button>
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Service fees from DB (approximate defaults)
const serviceFees = @json(\App\Models\Service::pluck('fee','slug'));

function updateFee(select) {
    const fee = serviceFees[select.value] ?? 0;
    document.getElementById('service_fee').value = parseFloat(fee).toFixed(2);
}

// Parishioner search
let pTimeout;
const pInput   = document.getElementById('p-search-input');
const pResults = document.getElementById('p-results');
const pWrap    = document.getElementById('p-search-wrap');
const pHidden  = document.getElementById('parishioner_id');

pInput.addEventListener('input', function () {
    clearTimeout(pTimeout);
    const q = this.value.trim();
    if (q.length < 2) { pResults.classList.add('hidden'); return; }
    pTimeout = setTimeout(() => {
        fetch(`/admin/parishioners/search?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => {
                pResults.innerHTML = data.length
                    ? data.map(p => `<div class="search-result-item" onclick="selectP(${p.id},'${esc(p.text)}','${esc(p.extra||'')}')"><div class="name">${esc(p.text)}</div><div class="meta">ID #${p.id}${p.extra?' · '+esc(p.extra):''}</div></div>`).join('')
                    : '<div class="search-result-item"><span class="name" style="color:#94a3b8">No results found</span></div>';
                pResults.classList.remove('hidden');
            });
    }, 280);
});

function selectP(id, name, extra) {
    pHidden.value = id;
    document.getElementById('p-selected-name').textContent = name;
    document.getElementById('p-selected-meta').textContent = `ID #${id}${extra?' · '+extra:''}`;
    document.getElementById('p-selected').classList.remove('hidden');
    pWrap.classList.add('hidden');
    pResults.classList.add('hidden');
}

function clearParishioner() {
    pHidden.value = '';
    document.getElementById('p-selected').classList.add('hidden');
    pWrap.classList.remove('hidden');
    pInput.value = '';
    pInput.focus();
}

function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }

document.addEventListener('click', e => {
    if (!e.target.closest('#p-search-wrap')) pResults.classList.add('hidden');
});
</script>
@endpush
