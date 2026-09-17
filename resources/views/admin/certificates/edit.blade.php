@extends('layouts.app')
@section('title', 'Edit Certificate — ' . $certificate->certificate_number)
@section('page-title', 'Edit Certificate')

@section('content')
<div class="py-6 max-w-3xl">

    {{-- Back navigation --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.certificates.show', $certificate) }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Certificate</h1>
            <p class="text-sm text-gray-500 font-mono">{{ $certificate->certificate_number }}</p>
        </div>
    </div>

    {{-- Parishioner info (read-only) --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 flex items-center gap-4 mb-5">
        <div class="w-11 h-11 rounded-full bg-blue-600 flex items-center justify-center text-white font-extrabold text-lg flex-shrink-0">
            {{ substr($certificate->parishioner->first_name, 0, 1) }}
        </div>
        <div>
            <p class="font-bold text-blue-900 text-base">{{ $certificate->parishioner->full_name }}</p>
            <p class="text-xs text-blue-600 mt-0.5">
                {{ $certificate->parishioner->contact_number ?? 'No contact' }}
                @if($certificate->parishioner->birthdate)
                · Born {{ $certificate->parishioner->birthdate->format('M d, Y') }}
                @endif
            </p>
        </div>
        <span class="ml-auto inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
            {{ $certificate->getTypeLabel() }}
        </span>
    </div>

    <form method="POST" action="{{ route('admin.certificates.update', $certificate) }}" class="space-y-5">
        @csrf @method('PUT')

        {{-- ── CERTIFICATE DETAILS ──────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
            <h2 class="font-bold text-gray-800 text-sm uppercase tracking-wide border-b border-gray-100 pb-2">
                Certificate Details
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Certificate Type <span class="text-red-500">*</span></label>
                    <select name="type" required class="form-select w-full @error('type') border-red-400 @enderror">
                        @foreach(\App\Models\Certificate::TYPES as $val => $label)
                        <option value="{{ $val }}" @selected(old('type', $certificate->type) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Issued Date <span class="text-red-500">*</span></label>
                    <input type="date" name="issued_date"
                           value="{{ old('issued_date', $certificate->issued_date->format('Y-m-d')) }}"
                           required class="form-input w-full @error('issued_date') border-red-400 @enderror">
                    @error('issued_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="form-select w-full @error('status') border-red-400 @enderror">
                        <option value="draft"    @selected(old('status', $certificate->status) === 'draft')>Draft</option>
                        <option value="issued"   @selected(old('status', $certificate->status) === 'issued')>Issued</option>
                        <option value="released" @selected(old('status', $certificate->status) === 'released')>Released</option>
                        <option value="revoked"  @selected(old('status', $certificate->status) === 'revoked')>Revoked</option>
                    </select>
                    @error('status')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose"
                           value="{{ old('purpose', $certificate->purpose) }}"
                           class="form-input w-full"
                           placeholder="e.g. For school enrollment, civil registration…">
                </div>
            </div>

            <div>
                <label class="form-label">Internal Notes</label>
                <textarea name="notes" rows="2" class="form-input w-full"
                          placeholder="Internal notes (not printed on certificate)…">{{ old('notes', $certificate->notes) }}</textarea>
            </div>
        </div>

        {{-- ── SACRAMENTAL RECORD FIELDS ───────────────────────────────── --}}
        @if($certificate->sacramentalRecord)
        <div class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-6 space-y-5">
            <h2 class="font-bold text-gray-800 text-sm uppercase tracking-wide border-b border-gray-100 pb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Sacramental Record Details
                <span class="text-xs font-normal text-gray-400 normal-case tracking-normal">
                    ({{ \App\Models\SacramentalRecord::TYPES[$certificate->sacramentalRecord->type] ?? ucfirst($certificate->sacramentalRecord->type) }})
                </span>
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Date Administered --}}
                <div>
                    <label class="form-label">Date Administered</label>
                    <input type="date" name="rec_date_administered"
                           value="{{ old('rec_date_administered', $certificate->sacramentalRecord->date_administered?->format('Y-m-d')) }}"
                           class="form-input w-full">
                    <p class="text-xs text-gray-400 mt-1">Date the sacrament was performed</p>
                </div>

                {{-- Officiating Priest / Celebrant --}}
                <div>
                    <label class="form-label">Officiating Priest</label>
                    <input type="text" name="rec_celebrant"
                           value="{{ old('rec_celebrant', $certificate->sacramentalRecord->celebrant) }}"
                           class="form-input w-full"
                           placeholder="e.g. Rev. Fr. Erwin S. Sanchez">
                </div>

                {{-- Venue --}}
                <div>
                    <label class="form-label">Venue / Location</label>
                    <input type="text" name="rec_venue"
                           value="{{ old('rec_venue', $certificate->sacramentalRecord->venue) }}"
                           class="form-input w-full"
                           placeholder="e.g. Mary Help of Christians Parish">
                </div>

                {{-- Parents (stored in notes field) --}}
                <div>
                    <label class="form-label">Parents' Names</label>
                    <input type="text" name="rec_notes"
                           value="{{ old('rec_notes', $certificate->sacramentalRecord->notes) }}"
                           class="form-input w-full"
                           placeholder="e.g. Juan Dela Cruz and Maria Santos Dela Cruz">
                    <p class="text-xs text-gray-400 mt-1">Displayed as "Parents" on the certificate</p>
                </div>
            </div>

            {{-- Godparents (ninong / ninang) --}}
            @php
                $godparents = $certificate->sacramentalRecord->godparents ?? [];
                $ninong = $godparents[0] ?? '';
                $ninang = $godparents[1] ?? '';
                // Additional godparents beyond the first two
                $extraGodparents = array_slice($godparents, 2);
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Godfather (Ninong)</label>
                    <input type="text" name="rec_godparents[]"
                           value="{{ old('rec_godparents.0', $ninong) }}"
                           class="form-input w-full"
                           placeholder="First godparent's full name">
                </div>
                <div>
                    <label class="form-label">Godmother (Ninang)</label>
                    <input type="text" name="rec_godparents[]"
                           value="{{ old('rec_godparents.1', $ninang) }}"
                           class="form-input w-full"
                           placeholder="Second godparent's full name">
                </div>
            </div>

            {{-- Additional godparents --}}
            <div id="extra-godparents" class="space-y-3">
                @foreach($extraGodparents as $i => $gp)
                <div class="flex gap-2 items-center">
                    <input type="text" name="rec_godparents[]"
                           value="{{ old('rec_godparents.' . ($i + 2), $gp) }}"
                           class="form-input flex-1"
                           placeholder="Additional godparent…">
                    <button type="button" onclick="this.closest('div').remove()"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @endforeach
            </div>
            <button type="button" onclick="addGodparent()"
                    class="text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add another godparent
            </button>

            {{-- Sponsors --}}
            @php $sponsors = $certificate->sacramentalRecord->sponsors ?? []; @endphp
            <div>
                <label class="form-label">Sponsors</label>
                <div id="sponsors-list" class="space-y-2">
                    @forelse($sponsors as $i => $sp)
                    <div class="flex gap-2 items-center">
                        <input type="text" name="rec_sponsors[]"
                               value="{{ old('rec_sponsors.' . $i, $sp) }}"
                               class="form-input flex-1"
                               placeholder="Sponsor's full name">
                        <button type="button" onclick="this.closest('div').remove()"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @empty
                    <div class="flex gap-2 items-center">
                        <input type="text" name="rec_sponsors[]"
                               class="form-input flex-1"
                               placeholder="Sponsor's full name">
                        <button type="button" onclick="this.closest('div').remove()"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @endforelse
                </div>
                <button type="button" onclick="addSponsor()"
                        class="mt-2 text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add sponsor
                </button>
            </div>

            {{-- Witnesses --}}
            @php $witnesses = $certificate->sacramentalRecord->witnesses ?? []; @endphp
            <div>
                <label class="form-label">Witnesses</label>
                <div id="witnesses-list" class="space-y-2">
                    @forelse($witnesses as $i => $wt)
                    <div class="flex gap-2 items-center">
                        <input type="text" name="rec_witnesses[]"
                               value="{{ old('rec_witnesses.' . $i, $wt) }}"
                               class="form-input flex-1"
                               placeholder="Witness's full name">
                        <button type="button" onclick="this.closest('div').remove()"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @empty
                    <div class="flex gap-2 items-center">
                        <input type="text" name="rec_witnesses[]"
                               class="form-input flex-1"
                               placeholder="Witness's full name">
                        <button type="button" onclick="this.closest('div').remove()"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @endforelse
                </div>
                <button type="button" onclick="addWitness()"
                        class="mt-2 text-xs font-semibold text-indigo-600 hover:underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add witness
                </button>
            </div>

            {{-- Register / Page / Line --}}
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Register No.</label>
                    <input type="text" name="rec_register_number"
                           value="{{ old('rec_register_number', $certificate->sacramentalRecord->register_number) }}"
                           class="form-input w-full font-mono"
                           placeholder="e.g. B-2026-001">
                </div>
                <div>
                    <label class="form-label">Page No.</label>
                    <input type="text" name="rec_page_number"
                           value="{{ old('rec_page_number', $certificate->sacramentalRecord->page_number) }}"
                           class="form-input w-full font-mono"
                           placeholder="e.g. 12">
                </div>
                <div>
                    <label class="form-label">Line No.</label>
                    <input type="text" name="rec_line_number"
                           value="{{ old('rec_line_number', $certificate->sacramentalRecord->line_number) }}"
                           class="form-input w-full font-mono"
                           placeholder="e.g. 5">
                </div>
            </div>

            <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 flex items-start gap-2">
                <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Changes here update the linked Sacramental Record and will be reflected on the regenerated certificate PDF.
            </p>
        </div>
        @else
        <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl px-5 py-4 text-center">
            <p class="text-sm text-gray-500">No sacramental record is linked to this certificate.</p>
            <p class="text-xs text-gray-400 mt-1">Link a sacramental record to enable editing of baptism/sacrament details.</p>
        </div>
        @endif

        {{-- Action buttons --}}
        <div class="flex flex-wrap gap-3 items-center">
            <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl transition shadow-sm shadow-blue-200">
                Save Changes & Regenerate PDF
            </button>
            <a href="{{ route('admin.certificates.show', $certificate) }}"
               class="px-5 py-2.5 border border-gray-200 text-gray-600 hover:bg-gray-50 font-semibold text-sm rounded-xl transition">
                Cancel
            </a>
            <form action="{{ route('admin.certificates.destroy', $certificate) }}" method="POST"
                  class="ml-auto" onsubmit="return confirm('Delete this certificate? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-5 py-2.5 border border-red-200 text-red-600 hover:bg-red-50 font-semibold text-sm rounded-xl transition">
                    Delete Certificate
                </button>
            </form>
        </div>

    </form>
</div>

@push('scripts')
<script>
function addGodparent() {
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-center';
    div.innerHTML = `
        <input type="text" name="rec_godparents[]" class="form-input flex-1" placeholder="Additional godparent…">
        <button type="button" onclick="this.closest('div').remove()"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;
    document.getElementById('extra-godparents').appendChild(div);
}

function addSponsor() {
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-center';
    div.innerHTML = `
        <input type="text" name="rec_sponsors[]" class="form-input flex-1" placeholder="Sponsor's full name">
        <button type="button" onclick="this.closest('div').remove()"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;
    document.getElementById('sponsors-list').appendChild(div);
}

function addWitness() {
    const div = document.createElement('div');
    div.className = 'flex gap-2 items-center';
    div.innerHTML = `
        <input type="text" name="rec_witnesses[]" class="form-input flex-1" placeholder="Witness's full name">
        <button type="button" onclick="this.closest('div').remove()"
                class="w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;
    document.getElementById('witnesses-list').appendChild(div);
}
</script>
@endpush

@endsection
