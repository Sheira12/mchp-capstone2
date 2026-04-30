@extends('layouts.app')

@section('title', 'New Certificate')
@section('page-title', 'New Certificate')

@push('styles')
<style>
.search-results {
    position: absolute;
    z-index: 50;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    width: 100%;
    max-height: 260px;
    overflow-y: auto;
    margin-top: 4px;
}
.search-result-item {
    padding: 0.625rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f8faff;
    transition: background 0.15s;
}
.search-result-item:last-child { border-bottom: none; }
.search-result-item:hover { background: #eff6ff; }
.search-result-item .name { font-weight: 600; font-size: 0.875rem; color: #0f172a; }
.search-result-item .meta { font-size: 0.75rem; color: #94a3b8; margin-top: 1px; }
.selected-parishioner {
    background: #eff6ff;
    border: 1.5px solid #bfdbfe;
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
</style>
@endpush

@section('content')
<div class="py-6 max-w-2xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.certificates.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">New Certificate</h1>
            <p class="text-sm text-gray-500">Issue a certificate to a parishioner</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.certificates.store') }}" class="space-y-5">
            @csrf

            {{-- ── Parishioner Search ── --}}
            <div>
                <label class="form-label">Parishioner <span class="text-red-500">*</span></label>

                @if($parishioner)
                    {{-- Pre-filled from URL param --}}
                    <input type="hidden" name="parishioner_id" value="{{ $parishioner->id }}">
                    <div class="selected-parishioner">
                        <div>
                            <p class="font-bold text-blue-900 text-sm">{{ $parishioner->full_name }}</p>
                            <p class="text-xs text-blue-600 mt-0.5">ID #{{ $parishioner->id }} · {{ $parishioner->contact_number ?? 'No contact' }}</p>
                        </div>
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                @else
                    {{-- Live search --}}
                    <div class="relative" id="parishioner-search-wrap">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="parishioner-search-input"
                                   placeholder="Type name to search parishioner…"
                                   class="form-input w-full pl-9"
                                   autocomplete="off">
                        </div>
                        <div id="parishioner-results" class="search-results hidden"></div>
                    </div>

                    {{-- Selected display --}}
                    <div id="selected-display" class="hidden mt-2 selected-parishioner">
                        <div>
                            <p id="selected-name" class="font-bold text-blue-900 text-sm"></p>
                            <p id="selected-meta" class="text-xs text-blue-600 mt-0.5"></p>
                        </div>
                        <button type="button" onclick="clearParishioner()" class="text-blue-400 hover:text-red-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <input type="hidden" name="parishioner_id" id="parishioner_id" value="{{ old('parishioner_id') }}" required>
                @endif

                @error('parishioner_id')
                <p class="form-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- ── Linked Sacramental Record ── --}}
            @if($sacramentalRecord)
            <div>
                <label class="form-label">Linked Sacramental Record</label>
                <input type="hidden" name="sacramental_record_id" value="{{ $sacramentalRecord->id }}">
                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-700">
                    <span class="font-semibold capitalize">{{ str_replace('_', ' ', $sacramentalRecord->type) }}</span>
                    — {{ $sacramentalRecord->date_administered->format('F d, Y') }}
                    <span class="text-gray-400 ml-2">ID #{{ $sacramentalRecord->id }}</span>
                </div>
            </div>
            @else
            <div>
                <label class="form-label">
                    Linked Sacramental Record
                    <span class="text-gray-400 text-xs font-normal">(optional — search by parishioner name)</span>
                </label>

                {{-- Record search --}}
                <div class="relative" id="record-search-wrap">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" id="record-search-input"
                               placeholder="Search sacramental records… (or leave blank)"
                               class="form-input w-full pl-9"
                               autocomplete="off">
                    </div>
                    <div id="record-results" class="search-results hidden"></div>
                </div>

                {{-- Selected record display --}}
                <div id="record-selected" class="hidden mt-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3 flex items-center justify-between">
                    <div>
                        <p id="record-selected-text" class="font-semibold text-sm text-green-900"></p>
                        <p id="record-selected-meta" class="text-xs text-green-600 mt-0.5"></p>
                    </div>
                    <button type="button" onclick="clearRecord()" class="text-green-400 hover:text-red-500 transition ml-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <input type="hidden" name="sacramental_record_id" id="sacramental_record_id" value="">
                <p class="text-xs text-gray-400 mt-1">Leave blank to create a certificate without linking to a sacramental record.</p>
                @error('sacramental_record_id')<p class="form-error mt-1">{{ $message }}</p>@enderror
            </div>
            @endif

            {{-- ── Certificate Type ── --}}
            <div>
                <label class="form-label">Certificate Type <span class="text-red-500">*</span></label>
                <select name="type" required class="form-select w-full @error('type') border-red-400 @enderror">
                    <option value="">Select type…</option>
                    @foreach(\App\Models\Certificate::TYPES as $val => $label)
                    <option value="{{ $val }}" @selected(old('type', $sacramentalRecord?->type) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- ── Issued Date ── --}}
            <div>
                <label class="form-label">Issued Date <span class="text-red-500">*</span></label>
                <input type="date" name="issued_date" value="{{ old('issued_date', now()->toDateString()) }}"
                       required class="form-input w-full">
            </div>

            {{-- ── Purpose ── --}}
            <div>
                <label class="form-label">Purpose <span class="text-gray-400 text-xs">(optional)</span></label>
                <input type="text" name="purpose" value="{{ old('purpose') }}"
                       class="form-input w-full"
                       placeholder="e.g. For school enrollment, civil registration, travel…">
            </div>

            {{-- ── Notes ── --}}
            <div>
                <label class="form-label">Notes <span class="text-gray-400 text-xs">(optional)</span></label>
                <textarea name="notes" rows="2" class="form-input w-full"
                          placeholder="Internal notes…">{{ old('notes') }}</textarea>
            </div>

            {{-- ── Actions ── --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-7 py-2.5 rounded-xl hover:bg-blue-700 shadow-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Generate Certificate
                </button>
                <a href="{{ route('admin.certificates.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let searchTimeout;
const searchInput  = document.getElementById('parishioner-search-input');
const resultsDiv   = document.getElementById('parishioner-results');
const selectedDiv  = document.getElementById('selected-display');
const hiddenInput  = document.getElementById('parishioner_id');
const searchWrap   = document.getElementById('parishioner-search-wrap');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const q = this.value.trim();
        if (q.length < 2) { resultsDiv.classList.add('hidden'); return; }

        searchTimeout = setTimeout(() => {
            fetch(`/admin/parishioners/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.length) {
                        resultsDiv.innerHTML = '<div class="search-result-item"><span class="name text-gray-400">No results found</span></div>';
                    } else {
                        resultsDiv.innerHTML = data.map(p => `
                            <div class="search-result-item" onclick="selectParishioner(${p.id}, '${escapeHtml(p.text)}', '${escapeHtml(p.extra || '')}')">
                                <div class="name">${escapeHtml(p.text)}</div>
                                <div class="meta">ID #${p.id}${p.extra ? ' · ' + escapeHtml(p.extra) : ''}</div>
                            </div>
                        `).join('');
                    }
                    resultsDiv.classList.remove('hidden');
                })
                .catch(() => resultsDiv.classList.add('hidden'));
        }, 280);
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') resultsDiv.classList.add('hidden');
    });
}

function selectParishioner(id, name, extra) {
    hiddenInput.value = id;
    document.getElementById('selected-name').textContent = name;
    document.getElementById('selected-meta').textContent = `ID #${id}${extra ? ' · ' + extra : ''}`;
    selectedDiv.classList.remove('hidden');
    searchWrap.classList.add('hidden');
    resultsDiv.classList.add('hidden');
}

function clearParishioner() {
    hiddenInput.value = '';
    selectedDiv.classList.add('hidden');
    searchWrap.classList.remove('hidden');
    searchInput.value = '';
    searchInput.focus();
}

function escapeHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    if (resultsDiv && !e.target.closest('#parishioner-search-wrap')) {
        resultsDiv.classList.add('hidden');
    }
});

// ── Sacramental Record Search ──
let recTimeout;
const recInput   = document.getElementById('record-search-input');
const recResults = document.getElementById('record-results');
const recWrap    = document.getElementById('record-search-wrap');
const recHidden  = document.getElementById('sacramental_record_id');

if (recInput) {
    recInput.addEventListener('input', function () {
        clearTimeout(recTimeout);
        const q = this.value.trim();
        if (q.length < 2) { recResults.classList.add('hidden'); return; }

        recTimeout = setTimeout(() => {
            fetch(`/admin/sacramental-records/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.length) {
                        recResults.innerHTML = '<div class="search-result-item"><span class="name" style="color:#94a3b8">No records found</span></div>';
                    } else {
                        recResults.innerHTML = data.map(r => `
                            <div class="search-result-item" onclick="selectRecord(${r.id}, '${escapeHtml(r.text)}', '${escapeHtml(r.meta)}')">
                                <div class="name">${escapeHtml(r.text)}</div>
                                <div class="meta">${escapeHtml(r.meta)}</div>
                            </div>
                        `).join('');
                    }
                    recResults.classList.remove('hidden');
                })
                .catch(() => recResults.classList.add('hidden'));
        }, 280);
    });

    recInput.addEventListener('keydown', e => {
        if (e.key === 'Escape') recResults.classList.add('hidden');
    });
}

function selectRecord(id, text, meta) {
    recHidden.value = id;
    document.getElementById('record-selected-text').textContent = text;
    document.getElementById('record-selected-meta').textContent = meta;
    document.getElementById('record-selected').classList.remove('hidden');
    recWrap.classList.add('hidden');
    recResults.classList.add('hidden');
}

function clearRecord() {
    recHidden.value = '';
    document.getElementById('record-selected').classList.add('hidden');
    recWrap.classList.remove('hidden');
    recInput.value = '';
    recInput.focus();
}

document.addEventListener('click', function(e) {
    if (recResults && !e.target.closest('#record-search-wrap')) {
        recResults.classList.add('hidden');
    }
});
</script>
@endpush
