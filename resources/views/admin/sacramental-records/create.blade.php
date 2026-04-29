@extends('layouts.app')

@section('title', 'New Sacramental Record')
@section('page-title', 'New Sacramental Record')

@section('content')
<div class="py-6 max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.sacramental-records.store') }}" class="space-y-5">
            @csrf

            {{-- Parishioner --}}
            <div>
                <label class="form-label">Parishioner <span class="text-red-500">*</span></label>
                @if($parishioner)
                    <input type="hidden" name="parishioner_id" value="{{ $parishioner->id }}">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5 text-sm font-semibold text-blue-900">
                        {{ $parishioner->full_name }} <span class="text-blue-400 font-normal">· ID #{{ $parishioner->id }}</span>
                    </div>
                @else
                    <div class="relative" id="p-search-wrap">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="parishioner-search" placeholder="Type name to search…"
                                   class="form-input w-full pl-9" autocomplete="off">
                        </div>
                        <div id="parishioner-results" class="hidden absolute z-50 bg-white border border-gray-200 rounded-xl shadow-xl mt-1 w-full max-h-56 overflow-y-auto"></div>
                    </div>
                    <div id="p-selected" class="hidden mt-2 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5 flex items-center justify-between">
                        <div>
                            <p id="p-selected-name" class="font-bold text-sm text-blue-900"></p>
                            <p id="p-selected-meta" class="text-xs text-blue-500 mt-0.5"></p>
                        </div>
                        <button type="button" onclick="clearP()" class="text-blue-400 hover:text-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <input type="hidden" name="parishioner_id" id="parishioner_id" value="{{ old('parishioner_id') }}" required>
                    @error('parishioner_id')<p class="form-error">{{ $message }}</p>@enderror
                @endif
            </div>

            {{-- Type --}}
            <div>
                <label class="form-label">Sacrament Type <span class="text-red-500">*</span></label>
                <select name="type" required class="form-select w-full @error('type') border-red-400 @enderror" id="sacrament-type">
                    <option value="">Select type…</option>
                    <option value="baptism" @selected(old('type') === 'baptism')>Baptism</option>
                    <option value="first_communion" @selected(old('type') === 'first_communion')>First Communion</option>
                    <option value="confirmation" @selected(old('type') === 'confirmation')>Confirmation</option>
                    <option value="marriage" @selected(old('type') === 'marriage')>Marriage</option>
                    <option value="death_burial" @selected(old('type') === 'death_burial')>Death/Burial</option>
                </select>
                @error('type')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Marriage spouse --}}
            <div id="spouse-field" class="hidden">
                <label class="form-label">Spouse (Parishioner)</label>
                <input type="text" id="spouse-search" placeholder="Search spouse name…"
                       class="form-input w-full" autocomplete="off">
                <input type="hidden" name="spouse_parishioner_id" id="spouse_parishioner_id">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date Administered <span class="text-red-500">*</span></label>
                    <input type="date" name="date_administered" value="{{ old('date_administered') }}" required
                           class="form-input w-full @error('date_administered') border-red-400 @enderror">
                    @error('date_administered')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Celebrant <span class="text-red-500">*</span></label>
                    <input type="text" name="celebrant" value="{{ old('celebrant', config('parish.priest')) }}" required
                           class="form-input w-full">
                </div>
            </div>

            <div>
                <label class="form-label">Venue</label>
                <input type="text" name="venue" value="{{ old('venue', config('parish.name')) }}"
                       class="form-input w-full">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Register Number</label>
                    <input type="text" name="register_number" value="{{ old('register_number') }}"
                           class="form-input w-full" placeholder="e.g. B-2024-001">
                </div>
                <div>
                    <label class="form-label">Page Number</label>
                    <input type="text" name="page_number" value="{{ old('page_number') }}"
                           class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Line Number</label>
                    <input type="text" name="line_number" value="{{ old('line_number') }}"
                           class="form-input w-full">
                </div>
            </div>

            {{-- Godparents --}}
            <div id="godparents-section">
                <label class="form-label">Godparents / Sponsors</label>
                <div id="godparents-list" class="space-y-2">
                    @foreach(old('godparents', ['']) as $i => $gp)
                    <div class="flex gap-2">
                        <input type="text" name="godparents[]" value="{{ $gp }}"
                               class="form-input flex-1" placeholder="Full name">
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">✕</button>
                    </div>
                    @endforeach
                </div>
                <button type="button" onclick="addGodparent()" class="mt-2 text-sm text-blue-600 hover:underline">+ Add godparent</button>
            </div>

            {{-- Witnesses --}}
            <div id="witnesses-section" class="hidden">
                <label class="form-label">Witnesses</label>
                <div id="witnesses-list" class="space-y-2">
                    <div class="flex gap-2">
                        <input type="text" name="witnesses[]" class="form-input flex-1" placeholder="Full name">
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">✕</button>
                    </div>
                </div>
                <button type="button" onclick="addWitness()" class="mt-2 text-sm text-blue-600 hover:underline">+ Add witness</button>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input w-full">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save Record</button>
                <a href="{{ route('admin.sacramental-records.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Show/hide spouse and witnesses based on type
document.getElementById('sacrament-type')?.addEventListener('change', function() {
    const isMarriage = this.value === 'marriage';
    document.getElementById('spouse-field').classList.toggle('hidden', !isMarriage);
    document.getElementById('witnesses-section').classList.toggle('hidden', !isMarriage);
});

function addGodparent() {
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = '<input type="text" name="godparents[]" class="form-input flex-1" placeholder="Full name"><button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">✕</button>';
    document.getElementById('godparents-list').appendChild(div);
}

function addWitness() {
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = '<input type="text" name="witnesses[]" class="form-input flex-1" placeholder="Full name"><button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">✕</button>';
    document.getElementById('witnesses-list').appendChild(div);
}

// Parishioner live search
let pTimeout;
const pInput   = document.getElementById('parishioner-search');
const pResults = document.getElementById('parishioner-results');
const pWrap    = document.getElementById('p-search-wrap');
const pHidden  = document.getElementById('parishioner_id');

if (pInput) {
    pInput.addEventListener('input', function () {
        clearTimeout(pTimeout);
        const q = this.value.trim();
        if (q.length < 2) { pResults?.classList.add('hidden'); return; }
        pTimeout = setTimeout(() => {
            fetch(`/admin/parishioners/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    if (!pResults) return;
                    pResults.innerHTML = data.length
                        ? data.map(p => `<div class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-0" onclick="selectP(${p.id},'${p.text.replace(/'/g,"\\'")}','${(p.extra||'').replace(/'/g,"\\'")}')"><p class="font-semibold text-sm text-gray-900">${p.text}</p><p class="text-xs text-gray-400">ID #${p.id}${p.extra ? ' · '+p.extra : ''}</p></div>`).join('')
                        : '<div class="px-4 py-3 text-sm text-gray-400">No results found</div>';
                    pResults.classList.remove('hidden');
                });
        }, 280);
    });
}

function selectP(id, name, extra) {
    if (pHidden) pHidden.value = id;
    const sel = document.getElementById('p-selected');
    document.getElementById('p-selected-name').textContent = name;
    document.getElementById('p-selected-meta').textContent = `ID #${id}${extra ? ' · '+extra : ''}`;
    sel?.classList.remove('hidden');
    pWrap?.classList.add('hidden');
    pResults?.classList.add('hidden');
}

function clearP() {
    if (pHidden) pHidden.value = '';
    document.getElementById('p-selected')?.classList.add('hidden');
    pWrap?.classList.remove('hidden');
    if (pInput) { pInput.value = ''; pInput.focus(); }
}

document.addEventListener('click', e => {
    if (!e.target.closest('#p-search-wrap')) pResults?.classList.add('hidden');
});
</script>
@endpush
