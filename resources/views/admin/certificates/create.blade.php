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
/* Registry pre-fill indicator */
.from-registry {
    background: #f0fdf4;
    border: 1.5px solid #bbf7d0;
    border-radius: 0.625rem;
    padding: 0.75rem 1rem;
    font-size: 0.8125rem;
    color: #166534;
}
.registry-field { position: relative; }
.registry-badge {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.65rem;
    font-weight: 700;
    background: #dcfce7;
    color: #16a34a;
    padding: 1px 6px;
    border-radius: 9999px;
    pointer-events: none;
}
.field-section {
    background: #f8faff;
    border: 1px solid #e8edf5;
    border-radius: 1rem;
    padding: 1.25rem;
}
.field-section-title {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
</style>
@endpush

@section('content')
<div class="py-6 max-w-2xl" x-data="certForm()" x-init="init()">

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
                    <input type="hidden" name="parishioner_id" :value="parishionerId || '{{ $parishioner->id }}'">
                    <div class="selected-parishioner">
                        <div>
                            <p class="font-bold text-blue-900 text-sm">{{ $parishioner->full_name }}</p>
                            <p class="text-xs text-blue-600 mt-0.5">ID #{{ $parishioner->id }} · {{ $parishioner->contact_number ?? 'No contact' }}</p>
                        </div>
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                @else
                    <input type="hidden" name="parishioner_id" :value="parishionerId" required>
                    <div class="relative" id="parishioner-search-wrap" x-show="!parishionerId">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" id="parishioner-search-input"
                                   placeholder="Type name to search parishioner…"
                                   class="form-input w-full pl-9"
                                   autocomplete="off">
                        </div>
                        <div id="parishioner-results" class="search-results hidden"></div>
                    </div>
                    <div x-show="parishionerId" class="mt-2 selected-parishioner" style="display:none;">
                        <div>
                            <p class="font-bold text-blue-900 text-sm" x-text="parishionerName"></p>
                            <p class="text-xs text-blue-600 mt-0.5" x-text="parishionerMeta"></p>
                        </div>
                        <button type="button" @click="clearParishioner()" class="text-blue-400 hover:text-red-500 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @error('parishioner_id')
                <p class="form-error mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- ── Certificate Type ── --}}
            <div>
                <label class="form-label">Certificate Type <span class="text-red-500">*</span></label>
                <select name="type" required
                        class="form-select w-full @error('type') border-red-400 @enderror"
                        @change="onTypeChange($event.target.value)">
                    <option value="">Select type…</option>
                    @foreach(\App\Models\Certificate::TYPES as $val => $label)
                    <option value="{{ $val }}" @selected(old('type', $sacramentalRecord?->type) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- ── Record lookup status ── --}}
            <div x-show="certType && needsRecord" style="display:none;">
                {{-- Loading --}}
                <div x-show="recordLoading" class="flex items-center gap-2 text-sm text-blue-600 bg-blue-50 rounded-lg px-4 py-3">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/><path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/></svg>
                    Searching parish registry for a matching record…
                </div>
                {{-- Record found --}}
                <div x-show="!recordLoading && recordFound" class="from-registry" style="display:none;">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Record found in parish registry
                    </div>
                    <p class="text-xs opacity-75">Fields below have been pre-filled from the official record. Any manual changes will be flagged for staff review.</p>
                </div>
                {{-- No record found --}}
                <div x-show="!recordLoading && !recordFound && parishionerId" class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800" style="display:none;">
                    <div class="flex items-center gap-2 font-bold mb-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        No matching record found
                    </div>
                    <p class="text-xs opacity-80">No <span x-text="certTypeLabel"></span> record was found for this parishioner. You can still fill in the details manually — the certificate will be marked as Pending Verification for staff review.</p>
                </div>
            </div>

            {{-- ── Linked Record (hidden) ── --}}
            <input type="hidden" name="sacramental_record_id" :value="linkedRecordId">

            {{-- ════════════════════════════════════════════════════
                 TYPE-SPECIFIC SACRAMENTAL DETAIL FIELDS
                 Show/hide reactively. Fields auto-fill from registry.
                 Fields not relevant to the selected type are hidden
                 AND their name attributes are removed so stale values
                 are never submitted.
            ═══════════════════════════════════════════════════════ --}}

            {{-- ── BAPTISM fields ── --}}
            <template x-if="certType === 'baptism'">
                <div class="field-section">
                    <p class="field-section-title">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/></svg>
                        Baptism Record Details
                    </p>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="registry-field">
                                <label class="form-label text-xs">Date of Baptism</label>
                                <input type="date" name="date_administered" class="form-input w-full" :value="fields.date_administered" @input="fields.date_administered=$event.target.value; fields.modified=true">
                                <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                            </div>
                            <div class="registry-field">
                                <label class="form-label text-xs">Officiating Priest</label>
                                <input type="text" name="officiating_priest" class="form-input w-full" :value="fields.celebrant" @input="fields.celebrant=$event.target.value; fields.modified=true" placeholder="Rev. Fr. …">
                                <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                            </div>
                        </div>
                        <div class="registry-field">
                            <label class="form-label text-xs">Venue / Church</label>
                            <input type="text" name="venue" class="form-input w-full" :value="fields.venue" @input="fields.venue=$event.target.value; fields.modified=true" placeholder="Mary Help of Christians Parish…">
                            <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                        </div>
                        <div>
                            <label class="form-label text-xs">Parents' Names</label>
                            <input type="text" name="parents_names" class="form-input w-full" :value="fields.parents_names" @input="fields.parents_names=$event.target.value" placeholder="Father's name / Mother's name">
                        </div>
                        <div>
                            <label class="form-label text-xs">
                                Godfathers (Ninong)
                                <button type="button" @click="fields.ninong.push('')" class="ml-2 text-blue-500 hover:text-blue-700 text-xs font-bold">+ Add</button>
                            </label>
                            <template x-for="(n, idx) in fields.ninong" :key="'ninong-'+idx">
                                <div class="flex gap-2 mt-1">
                                    <input type="text" :name="'ninong['+idx+']'" class="form-input flex-1" :value="n" @input="fields.ninong[idx]=$event.target.value" placeholder="Ninong name">
                                    <button type="button" @click="fields.ninong.splice(idx,1)" class="text-gray-300 hover:text-red-400 transition px-1" x-show="fields.ninong.length > 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div>
                            <label class="form-label text-xs">
                                Godmothers (Ninang)
                                <button type="button" @click="fields.ninang.push('')" class="ml-2 text-blue-500 hover:text-blue-700 text-xs font-bold">+ Add</button>
                            </label>
                            <template x-for="(n, idx) in fields.ninang" :key="'ninang-'+idx">
                                <div class="flex gap-2 mt-1">
                                    <input type="text" :name="'ninang['+idx+']'" class="form-input flex-1" :value="n" @input="fields.ninang[idx]=$event.target.value" placeholder="Ninang name">
                                    <button type="button" @click="fields.ninang.splice(idx,1)" class="text-gray-300 hover:text-red-400 transition px-1" x-show="fields.ninang.length > 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="form-label text-xs">Register #</label><input type="text" name="register_no" class="form-input w-full" :value="fields.register_number" @input="fields.register_number=$event.target.value" placeholder="Reg #"></div>
                            <div><label class="form-label text-xs">Page #</label><input type="text" name="page_no" class="form-input w-full" :value="fields.page_number" @input="fields.page_number=$event.target.value" placeholder="Pg #"></div>
                            <div><label class="form-label text-xs">Line #</label><input type="text" name="line_no" class="form-input w-full" :value="fields.line_number" @input="fields.line_number=$event.target.value" placeholder="Ln #"></div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ── CONFIRMATION fields ── --}}
            <template x-if="certType === 'confirmation'">
                <div class="field-section">
                    <p class="field-section-title">✝️ Confirmation Record Details</p>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="registry-field">
                                <label class="form-label text-xs">Date of Confirmation</label>
                                <input type="date" name="date_administered" class="form-input w-full" :value="fields.date_administered" @input="fields.date_administered=$event.target.value; fields.modified=true">
                                <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                            </div>
                            <div class="registry-field">
                                <label class="form-label text-xs">Officiating Bishop / Priest</label>
                                <input type="text" name="officiating_priest" class="form-input w-full" :value="fields.celebrant" @input="fields.celebrant=$event.target.value; fields.modified=true" placeholder="Most Rev. …">
                                <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                            </div>
                        </div>
                        <div class="registry-field">
                            <label class="form-label text-xs">Venue / Church</label>
                            <input type="text" name="venue" class="form-input w-full" :value="fields.venue" @input="fields.venue=$event.target.value; fields.modified=true" placeholder="Church or venue name">
                            <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                        </div>
                        <div>
                            <label class="form-label text-xs">
                                Sponsor(s)
                                <button type="button" @click="fields.sponsors.push('')" class="ml-2 text-blue-500 hover:text-blue-700 text-xs font-bold">+ Add</button>
                            </label>
                            <template x-for="(s, idx) in fields.sponsors" :key="'sponsor-'+idx">
                                <div class="flex gap-2 mt-1">
                                    <input type="text" :name="'sponsors['+idx+']'" class="form-input flex-1" :value="s" @input="fields.sponsors[idx]=$event.target.value" placeholder="Sponsor name">
                                    <button type="button" @click="fields.sponsors.splice(idx,1)" class="text-gray-300 hover:text-red-400 transition px-1" x-show="fields.sponsors.length > 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div>
                            <label class="form-label text-xs">Parents' Names</label>
                            <input type="text" name="parents_names" class="form-input w-full" :value="fields.parents_names" @input="fields.parents_names=$event.target.value" placeholder="Father's name / Mother's name">
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="form-label text-xs">Register #</label><input type="text" name="register_no" class="form-input w-full" :value="fields.register_number" @input="fields.register_number=$event.target.value" placeholder="Reg #"></div>
                            <div><label class="form-label text-xs">Page #</label><input type="text" name="page_no" class="form-input w-full" :value="fields.page_number" @input="fields.page_number=$event.target.value" placeholder="Pg #"></div>
                            <div><label class="form-label text-xs">Line #</label><input type="text" name="line_no" class="form-input w-full" :value="fields.line_number" @input="fields.line_number=$event.target.value" placeholder="Ln #"></div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ── MARRIAGE fields ── --}}
            <template x-if="certType === 'marriage'">
                <div class="field-section">
                    <p class="field-section-title">💍 Marriage Record Details</p>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="registry-field">
                                <label class="form-label text-xs">Date of Marriage</label>
                                <input type="date" name="date_administered" class="form-input w-full" :value="fields.date_administered" @input="fields.date_administered=$event.target.value; fields.modified=true">
                                <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                            </div>
                            <div class="registry-field">
                                <label class="form-label text-xs">Officiating Priest</label>
                                <input type="text" name="officiating_priest" class="form-input w-full" :value="fields.celebrant" @input="fields.celebrant=$event.target.value; fields.modified=true" placeholder="Rev. Fr. …">
                                <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                            </div>
                        </div>
                        <div class="registry-field">
                            <label class="form-label text-xs">Venue / Church</label>
                            <input type="text" name="venue" class="form-input w-full" :value="fields.venue" @input="fields.venue=$event.target.value; fields.modified=true" placeholder="Church or venue name">
                            <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                        </div>
                        <div>
                            <label class="form-label text-xs">Spouse Name</label>
                            <input type="text" name="spouse_name" class="form-input w-full" :value="fields.spouse_name" @input="fields.spouse_name=$event.target.value" placeholder="Full name of spouse">
                        </div>
                        <div>
                            <label class="form-label text-xs">
                                Principal Sponsors (Ninong)
                                <button type="button" @click="fields.ninong.push('')" class="ml-2 text-blue-500 hover:text-blue-700 text-xs font-bold">+ Add</button>
                            </label>
                            <template x-for="(n, idx) in fields.ninong" :key="'ninong-'+idx">
                                <div class="flex gap-2 mt-1">
                                    <input type="text" :name="'ninong['+idx+']'" class="form-input flex-1" :value="n" @input="fields.ninong[idx]=$event.target.value" placeholder="Ninong name">
                                    <button type="button" @click="fields.ninong.splice(idx,1)" class="text-gray-300 hover:text-red-400 transition px-1" x-show="fields.ninong.length > 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div>
                            <label class="form-label text-xs">
                                Principal Sponsors (Ninang)
                                <button type="button" @click="fields.ninang.push('')" class="ml-2 text-blue-500 hover:text-blue-700 text-xs font-bold">+ Add</button>
                            </label>
                            <template x-for="(n, idx) in fields.ninang" :key="'ninang-'+idx">
                                <div class="flex gap-2 mt-1">
                                    <input type="text" :name="'ninang['+idx+']'" class="form-input flex-1" :value="n" @input="fields.ninang[idx]=$event.target.value" placeholder="Ninang name">
                                    <button type="button" @click="fields.ninang.splice(idx,1)" class="text-gray-300 hover:text-red-400 transition px-1" x-show="fields.ninang.length > 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-xs">Witness 1</label>
                                <input type="text" name="witness_1" class="form-input w-full" :value="fields.witness_1" @input="fields.witness_1=$event.target.value" placeholder="Witness 1 full name">
                            </div>
                            <div>
                                <label class="form-label text-xs">Witness 2</label>
                                <input type="text" name="witness_2" class="form-input w-full" :value="fields.witness_2" @input="fields.witness_2=$event.target.value" placeholder="Witness 2 full name">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="form-label text-xs">Register #</label><input type="text" name="register_no" class="form-input w-full" :value="fields.register_number" @input="fields.register_number=$event.target.value" placeholder="Reg #"></div>
                            <div><label class="form-label text-xs">Page #</label><input type="text" name="page_no" class="form-input w-full" :value="fields.page_number" @input="fields.page_number=$event.target.value" placeholder="Pg #"></div>
                            <div><label class="form-label text-xs">Line #</label><input type="text" name="line_no" class="form-input w-full" :value="fields.line_number" @input="fields.line_number=$event.target.value" placeholder="Ln #"></div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ── FIRST COMMUNION fields ── --}}
            <template x-if="certType === 'first_communion'">
                <div class="field-section">
                    <p class="field-section-title">🕊️ First Communion Record Details</p>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="registry-field">
                                <label class="form-label text-xs">Date of First Communion</label>
                                <input type="date" name="date_administered" class="form-input w-full" :value="fields.date_administered" @input="fields.date_administered=$event.target.value; fields.modified=true">
                                <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                            </div>
                            <div class="registry-field">
                                <label class="form-label text-xs">Officiating Priest</label>
                                <input type="text" name="officiating_priest" class="form-input w-full" :value="fields.celebrant" @input="fields.celebrant=$event.target.value; fields.modified=true" placeholder="Rev. Fr. …">
                                <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                            </div>
                        </div>
                        <div class="registry-field">
                            <label class="form-label text-xs">Venue / Church</label>
                            <input type="text" name="venue" class="form-input w-full" :value="fields.venue" @input="fields.venue=$event.target.value; fields.modified=true" placeholder="Church or venue name">
                            <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div><label class="form-label text-xs">Register #</label><input type="text" name="register_no" class="form-input w-full" :value="fields.register_number" @input="fields.register_number=$event.target.value" placeholder="Reg #"></div>
                            <div><label class="form-label text-xs">Page #</label><input type="text" name="page_no" class="form-input w-full" :value="fields.page_number" @input="fields.page_number=$event.target.value" placeholder="Pg #"></div>
                            <div><label class="form-label text-xs">Line #</label><input type="text" name="line_no" class="form-input w-full" :value="fields.line_number" @input="fields.line_number=$event.target.value" placeholder="Ln #"></div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ── DEATH/BURIAL fields ── --}}
            <template x-if="certType === 'death_burial'">
                <div class="field-section">
                    <p class="field-section-title">🕯️ Death / Burial Record Details</p>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-xs">Date of Death</label>
                                <input type="date" name="date_of_death" class="form-input w-full" :value="fields.date_of_death" @input="fields.date_of_death=$event.target.value" placeholder="Date of death">
                            </div>
                            <div class="registry-field">
                                <label class="form-label text-xs">Date of Burial</label>
                                <input type="date" name="date_administered" class="form-input w-full" :value="fields.date_administered" @input="fields.date_administered=$event.target.value; fields.modified=true">
                                <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                            </div>
                        </div>
                        <div class="registry-field">
                            <label class="form-label text-xs">Place of Burial</label>
                            <input type="text" name="venue" class="form-input w-full" :value="fields.venue" @input="fields.venue=$event.target.value; fields.modified=true" placeholder="Cemetery / burial church">
                            <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                        </div>
                        <div class="registry-field">
                            <label class="form-label text-xs">Officiating Priest</label>
                            <input type="text" name="officiating_priest" class="form-input w-full" :value="fields.celebrant" @input="fields.celebrant=$event.target.value; fields.modified=true" placeholder="Rev. Fr. …">
                            <span class="registry-badge" x-show="recordFound && !fields.modified">Registry</span>
                        </div>
                        <div>
                            <label class="form-label text-xs">Next of Kin / Family Contact</label>
                            <input type="text" name="next_of_kin" class="form-input w-full" :value="fields.next_of_kin" @input="fields.next_of_kin=$event.target.value" placeholder="Name and relationship">
                        </div>
                    </div>
                </div>
            </template>

            {{-- ── NO IMPEDIMENT fields ── --}}
            <template x-if="certType === 'no_impediment'">
                <div class="field-section">
                    <p class="field-section-title">📋 No Impediment Details</p>
                    <div class="space-y-3">
                        <div>
                            <label class="form-label text-xs">Baptism Reference</label>
                            <input type="text" name="baptism_reference" class="form-input w-full" :value="fields.baptism_reference" @input="fields.baptism_reference=$event.target.value" placeholder="Reg# / Parish / Date">
                        </div>
                        <div>
                            <label class="form-label text-xs">Confirmation Reference</label>
                            <input type="text" name="confirmation_reference" class="form-input w-full" :value="fields.confirmation_reference" @input="fields.confirmation_reference=$event.target.value" placeholder="Reg# / Parish / Date">
                        </div>
                        <div>
                            <label class="form-label text-xs">Civil Status</label>
                            <select name="civil_status" class="form-select w-full">
                                <option value="">Select…</option>
                                <option value="single">Single</option>
                                <option value="widowed">Widowed</option>
                                <option value="annulled">Annulled</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs">Remarks</label>
                            <textarea name="impediment_remarks" rows="2" class="form-input w-full" placeholder="Additional remarks…"></textarea>
                        </div>
                    </div>
                </div>
            </template>

            {{-- ── MEMBERSHIP fields ── --}}
            <template x-if="certType === 'membership'">
                <div class="field-section">
                    <p class="field-section-title">🏛️ Parish Membership Details</p>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-xs">Date Registered</label>
                                <input type="date" name="date_registered" class="form-input w-full" :value="fields.date_registered" @input="fields.date_registered=$event.target.value">
                            </div>
                            <div>
                                <label class="form-label text-xs">Years of Membership</label>
                                <input type="number" name="years_of_membership" min="0" class="form-input w-full" :value="fields.years_of_membership" @input="fields.years_of_membership=$event.target.value" placeholder="e.g. 5">
                            </div>
                        </div>
                        <div>
                            <label class="form-label text-xs">Parish Address</label>
                            <input type="text" name="parish_address" class="form-input w-full" :value="fields.parish_address" @input="fields.parish_address=$event.target.value" placeholder="Home address">
                        </div>
                    </div>
                </div>
            </template>

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
{{-- Alpine.js for reactive type-switching --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

<script>
function certForm() {
    return {
        // ── State ──────────────────────────────────────────────
        parishionerId:   '{{ old('parishioner_id', $parishioner?->id ?? '') }}',
        parishionerName: '',
        parishionerMeta: '',
        certType:        '{{ old('type', $sacramentalRecord?->type ?? '') }}',
        certTypeLabel:   '',
        linkedRecordId:  '{{ $sacramentalRecord?->id ?? '' }}',
        recordLoading:   false,
        recordFound:     false,
        needsRecord:     false,  // true for types that require a sacramental record

        // Type-specific field bag (shared across types — template x-if ensures
        // only the relevant fields are rendered in the DOM at any time, so
        // values from a previously selected type are never submitted).
        fields: {
            date_administered: '',
            celebrant: '',
            venue: '',
            register_number: '',
            page_number: '',
            line_number: '',
            parents_names: '',
            ninong: [''],
            ninang: [''],
            sponsors: [''],
            witness_1: '',
            witness_2: '',
            spouse_name: '',
            date_of_death: '',
            next_of_kin: '',
            baptism_reference: '',
            confirmation_reference: '',
            civil_status: '',
            date_registered: '',
            years_of_membership: '',
            parish_address: '',
            modified: false,  // track if admin has overridden a registry value
        },

        // Types that have a matching sacramental record
        sacTypes: ['baptism','confirmation','marriage','first_communion','death_burial'],
        typeLabels: {
            baptism: 'Baptismal', confirmation: 'Confirmation', marriage: 'Marriage',
            first_communion: 'First Communion', death_burial: 'Death/Burial',
            no_impediment: 'No Impediment', membership: 'Parish Membership',
        },

        // ── Lifecycle ──────────────────────────────────────────
        init() {
            // If parishioner + type are pre-set (e.g. from URL param), auto-populate
            @if($sacramentalRecord)
            this.populateFromRecord({!! json_encode([
                'found'             => true,
                'id'                => $sacramentalRecord->id,
                'date_administered' => $sacramentalRecord->date_administered?->format('Y-m-d'),
                'celebrant'         => $sacramentalRecord->celebrant ?? '',
                'venue'             => $sacramentalRecord->venue ?? '',
                'register_number'   => $sacramentalRecord->register_number ?? '',
                'page_number'       => $sacramentalRecord->page_number ?? '',
                'line_number'       => $sacramentalRecord->line_number ?? '',
                'godparents'        => is_array($sacramentalRecord->godparents) ? $sacramentalRecord->godparents : [],
                'sponsors'          => is_array($sacramentalRecord->sponsors)   ? $sacramentalRecord->sponsors   : [],
                'witnesses'         => is_array($sacramentalRecord->witnesses)  ? $sacramentalRecord->witnesses  : [],
            ]) !!});
            @endif

            if (this.certType) {
                this.needsRecord   = this.sacTypes.includes(this.certType);
                this.certTypeLabel = this.typeLabels[this.certType] || this.certType;
                if (this.needsRecord && this.parishionerId && !this.linkedRecordId) {
                    this.autoFetchRecord();
                }
            }
        },

        // ── Type changed ───────────────────────────────────────
        onTypeChange(val) {
            this.certType      = val;
            this.certTypeLabel = this.typeLabels[val] || val;
            this.needsRecord   = this.sacTypes.includes(val);
            // Clear previous record and field values when type changes
            this.linkedRecordId = '';
            this.recordFound    = false;
            this.resetFields();
            if (this.needsRecord && this.parishionerId) {
                this.autoFetchRecord();
            }
        },

        // ── Auto-fetch matching sacramental record ─────────────
        autoFetchRecord() {
            if (!this.parishionerId || !this.certType) return;
            this.recordLoading = true;
            this.recordFound   = false;
            fetch(`/admin/sacramental-records/fetch-for-certificate?parishioner_id=${encodeURIComponent(this.parishionerId)}&cert_type=${encodeURIComponent(this.certType)}`)
                .then(r => r.json())
                .then(data => {
                    this.recordLoading = false;
                    this.populateFromRecord(data);
                })
                .catch(() => { this.recordLoading = false; });
        },

        // ── Populate fields from registry data ─────────────────
        populateFromRecord(data) {
            if (!data.found) { this.recordFound = false; return; }
            this.recordFound    = true;
            this.linkedRecordId = data.id;

            const gps = Array.isArray(data.godparents) ? data.godparents : [];
            const sps = Array.isArray(data.sponsors)   ? data.sponsors   : [];
            const wts = Array.isArray(data.witnesses)  ? data.witnesses  : [];

            this.fields.date_administered = data.date_administered || '';
            this.fields.celebrant         = data.celebrant || '';
            this.fields.venue             = data.venue || '';
            this.fields.register_number   = data.register_number || '';
            this.fields.page_number       = data.page_number || '';
            this.fields.line_number       = data.line_number || '';
            // Godparents split by gender — if tagged, split; else put all in ninong
            this.fields.ninong  = gps.filter(g => !String(g).startsWith('NINANG:')).map(g => g.replace(/^NINONG:/,'')) || [''];
            this.fields.ninang  = gps.filter(g => String(g).startsWith('NINANG:')).map(g => g.replace(/^NINANG:/,''));
            if (!this.fields.ninong.length) this.fields.ninong = [''];
            if (!this.fields.ninang.length) this.fields.ninang = [''];
            this.fields.sponsors = sps.length ? sps : [''];
            this.fields.witness_1 = wts[0] || '';
            this.fields.witness_2 = wts[1] || '';
            this.fields.modified  = false;
        },

        resetFields() {
            this.fields = {
                date_administered: '', celebrant: '', venue: '',
                register_number: '', page_number: '', line_number: '',
                parents_names: '', ninong: [''], ninang: [''], sponsors: [''],
                witness_1: '', witness_2: '', spouse_name: '',
                date_of_death: '', next_of_kin: '',
                baptism_reference: '', confirmation_reference: '',
                civil_status: '', date_registered: '',
                years_of_membership: '', parish_address: '',
                modified: false,
            };
        },

        // ── Parishioner selected/cleared ───────────────────────
        selectParishioner(id, name, extra) {
            this.parishionerId   = String(id);
            this.parishionerName = name;
            this.parishionerMeta = `ID #${id}${extra ? ' · ' + extra : ''}`;
            if (this.needsRecord) this.autoFetchRecord();
        },

        clearParishioner() {
            this.parishionerId   = '';
            this.parishionerName = '';
            this.parishionerMeta = '';
            this.linkedRecordId  = '';
            this.recordFound     = false;
            this.resetFields();
        },
    };
}

// ── Parishioner live search (vanilla JS, outside Alpine) ──
(function () {
    const searchInput = document.getElementById('parishioner-search-input');
    const resultsDiv  = document.getElementById('parishioner-results');
    if (!searchInput) return;

    let t;
    searchInput.addEventListener('input', function () {
        clearTimeout(t);
        const q = this.value.trim();
        if (q.length < 2) { resultsDiv.classList.add('hidden'); return; }
        t = setTimeout(() => {
            fetch(`/admin/parishioners/search?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    resultsDiv.innerHTML = data.length
                        ? data.map(p => `<div class="search-result-item" onclick="window._certFormAlpine.selectParishioner(${p.id},'${esc(p.text)}','${esc(p.extra||'')}');document.getElementById('parishioner-search-input').value='';document.getElementById('parishioner-results').classList.add('hidden');">
                                <div class="name">${esc(p.text)}</div>
                                <div class="meta">ID #${p.id}${p.extra?' · '+esc(p.extra):''}</div>
                            </div>`).join('')
                        : '<div class="search-result-item"><span class="name" style="color:#94a3b8">No results found</span></div>';
                    resultsDiv.classList.remove('hidden');
                }).catch(() => resultsDiv.classList.add('hidden'));
        }, 280);
    });
    searchInput.addEventListener('keydown', e => { if (e.key === 'Escape') resultsDiv.classList.add('hidden'); });
    document.addEventListener('click', e => { if (!e.target.closest('#parishioner-search-wrap')) resultsDiv.classList.add('hidden'); });

    function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
})();

// Expose Alpine component instance so the vanilla search handler can call selectParishioner
document.addEventListener('alpine:init', () => {
    // Store reference after Alpine mounts
    requestAnimationFrame(() => {
        const el = document.querySelector('[x-data="certForm()"]');
        if (el && el._x_dataStack) {
            window._certFormAlpine = el._x_dataStack[0];
        }
    });
});
</script>
@endpush
