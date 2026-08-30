@extends('layouts.app')

@section('title', 'Parishioners')
@section('page-title', 'Parishioners')

@push('styles')
<style>
/* ── Parishioner card (mobile) ─────────────────────────────── */
.par-card {
    background:#fff;
    border:1px solid #e8edf5;
    border-radius:1rem;
    padding:1rem;
    display:flex;
    gap:0.875rem;
    align-items:flex-start;
    transition:box-shadow 0.15s;
}
.par-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.07); }
.par-avatar {
    width:48px;height:48px;border-radius:50%;
    object-fit:cover;flex-shrink:0;
    border:2px solid #e0e7ff;
}
.par-avatar-initials {
    width:48px;height:48px;border-radius:50%;flex-shrink:0;
    background:linear-gradient(135deg,#dbeafe,#e0e7ff);
    display:flex;align-items:center;justify-content:center;
    font-weight:700;font-size:0.9rem;color:#2563eb;
    border:2px solid #e0e7ff;
}
.par-name  { font-weight:700;font-size:0.9375rem;color:#0f172a;line-height:1.25; }
.par-meta  { font-size:0.75rem;color:#64748b;margin-top:2px; }
.par-chips { display:flex;flex-wrap:wrap;gap:0.375rem;margin-top:0.5rem; }
.chip {
    display:inline-flex;align-items:center;gap:4px;
    padding:2px 8px;border-radius:9999px;font-size:0.7rem;font-weight:600;
}
.chip-green  { background:#dcfce7;color:#15803d; }
.chip-gray   { background:#f1f5f9;color:#475569; }
.chip-blue   { background:#dbeafe;color:#1d4ed8; }
.chip-amber  { background:#fef3c7;color:#92400e; }
.par-actions { display:flex;gap:0.375rem;margin-top:0.625rem;flex-wrap:wrap; }
.par-actions .action-btn { font-size:0.7rem;padding:3px 9px; }
</style>
@endpush

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">
            <span class="font-semibold text-gray-800">{{ $parishioners->total() }}</span> parishioners found
        </p>
        <div class="flex gap-2">
            <a href="{{ route('admin.families.create') }}" class="btn-secondary text-sm">+ New Family</a>
            <a href="{{ route('admin.parishioners.create') }}" class="btn-primary text-sm">+ Add Parishioner</a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" data-live-search data-target="#parishioners-list" class="space-y-3">
            {{-- Row 1: search + barangay --}}
            <div class="flex flex-wrap gap-2">
                <div class="flex-1 min-w-0 relative" style="min-width:200px;">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                        </svg>
                    </div>
                    <input type="text" id="parishioner-search" name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by name, phone, email…"
                           class="form-input w-full text-sm pl-9"
                           autocomplete="off" data-live-input>
                    <div id="parishioner-dropdown"
                         class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-xl z-40 hidden mt-1 max-h-64 overflow-y-auto"></div>
                </div>
                <select name="barangay" class="form-select text-sm" data-live-input style="min-width:130px;">
                    <option value="">All Barangays</option>
                    @foreach($barangays as $b)
                    <option value="{{ $b }}" {{ request('barangay') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Row 2: family + sacrament + buttons --}}
            <div class="flex flex-wrap gap-2 items-center">
                <select name="family_id" class="form-select text-sm" data-live-input style="min-width:130px;">
                    <option value="">All Families</option>
                    @foreach($families as $f)
                    <option value="{{ $f->id }}" {{ request('family_id') == $f->id ? 'selected' : '' }}>{{ $f->family_name }}</option>
                    @endforeach
                </select>
                <select name="sacrament" class="form-select text-sm" data-live-input style="min-width:130px;">
                    <option value="">Any Sacrament</option>
                    @foreach(\App\Models\SacramentalRecord::TYPES as $key => $label)
                    <option value="{{ $key }}" {{ request('sacrament') === $key ? 'selected' : '' }}>Has {{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary text-sm px-5">Search</button>
                @if(request()->hasAny(['search','barangay','family_id','sacrament']))
                <a href="{{ route('admin.parishioners.index') }}" class="btn-secondary text-sm">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- ── MOBILE CARDS (hidden on lg+) ── --}}
    <div id="parishioners-list" class="space-y-3 lg:hidden">
        @forelse($parishioners as $parishioner)
        <div class="par-card">
            {{-- Avatar --}}
            @if($parishioner->photo_path)
            <img src="{{ str_starts_with($parishioner->photo_path, 'data:') ? $parishioner->photo_path : Storage::url($parishioner->photo_path) }}" class="par-avatar" alt="" onerror="this.style.display='none'">
            @else
            <div class="par-avatar-initials">
                {{ substr($parishioner->first_name,0,1) }}{{ substr($parishioner->last_name,0,1) }}
            </div>
            @endif

            {{-- Details --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <a href="{{ route('admin.parishioners.show', $parishioner) }}"
                           class="par-name hover:text-blue-700">
                            {{ $parishioner->full_name }}
                            @if($parishioner->is_head_of_family)
                            <span class="ml-1 text-xs text-blue-500 font-normal">(Head)</span>
                            @endif
                        </a>
                        @if($parishioner->birthdate)
                        <p class="par-meta">Age {{ $parishioner->age }}</p>
                        @endif
                    </div>
                    <span class="chip {{ $parishioner->is_active ? 'chip-green' : 'chip-gray' }} flex-shrink-0">
                        {{ $parishioner->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                {{-- Info chips --}}
                <div class="par-chips">
                    @if($parishioner->contact_number)
                    <span class="chip chip-gray">
                        <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $parishioner->contact_number }}
                    </span>
                    @endif
                    @if($parishioner->barangay)
                    <span class="chip chip-blue">
                        <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $parishioner->barangay }}
                    </span>
                    @endif
                    @if($parishioner->family)
                    <span class="chip chip-amber">
                        <svg style="width:10px;height:10px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        {{ $parishioner->family->family_name }}
                    </span>
                    @endif
                    @if($parishioner->sacramental_records_count > 0)
                    <span class="chip chip-gray">
                        {{ $parishioner->sacramental_records_count }} sacrament{{ $parishioner->sacramental_records_count > 1 ? 's' : '' }}
                    </span>
                    @endif
                    @if($parishioner->email)
                    <span class="chip chip-gray text-xs" style="font-size:0.65rem;">{{ $parishioner->email }}</span>
                    @endif
                </div>

                {{-- Action buttons --}}
                <div class="par-actions">
                    <a href="{{ route('admin.parishioners.show', $parishioner) }}" class="action-btn action-btn-view">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        View
                    </a>
                    <a href="{{ route('admin.parishioners.soa', $parishioner) }}" class="action-btn action-btn-gray">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        SOA
                    </a>
                    <a href="{{ route('admin.parishioners.edit', $parishioner) }}" class="action-btn action-btn-edit">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-gray-400 font-medium">No parishioners found.</p>
        </div>
        @endforelse

        @if($parishioners->hasPages())
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">
            {{ $parishioners->links() }}
        </div>
        @endif
    </div>

    {{-- ── DESKTOP TABLE (hidden below lg) ── --}}
    <div id="parishioners-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Contact</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Barangay</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Family</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Sacraments</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($parishioners as $parishioner)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($parishioner->photo_path)
                                <img src="{{ str_starts_with($parishioner->photo_path, 'data:') ? $parishioner->photo_path : Storage::url($parishioner->photo_path) }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" onerror="this.style.display='none'">
                                @else
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-blue-700 font-semibold text-xs flex-shrink-0">
                                    {{ substr($parishioner->first_name,0,1) }}{{ substr($parishioner->last_name,0,1) }}
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.parishioners.show', $parishioner) }}"
                                       class="font-semibold text-gray-900 hover:text-blue-700 leading-tight">
                                        {{ $parishioner->full_name }}
                                        @if($parishioner->is_head_of_family)
                                        <span class="ml-1 text-xs font-normal text-blue-500">(Head)</span>
                                        @endif
                                    </a>
                                    @if($parishioner->birthdate)
                                    <p class="text-xs text-gray-400 mt-0.5">Age {{ $parishioner->age }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-gray-700 text-sm">{{ $parishioner->contact_number ?? '—' }}</p>
                            @if($parishioner->email)
                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[160px]">{{ $parishioner->email }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm">{{ $parishioner->barangay ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($parishioner->family)
                            <a href="{{ route('admin.families.show', $parishioner->family) }}"
                               class="text-sm text-blue-600 hover:underline">{{ $parishioner->family->family_name }}</a>
                            @else
                            <span class="text-gray-400 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($parishioner->sacramental_records_count > 0)
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                {{ $parishioner->sacramental_records_count }}
                            </span>
                            @else
                            <span class="text-gray-300 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                {{ $parishioner->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $parishioner->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                {{ $parishioner->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.parishioners.show', $parishioner) }}" class="action-btn action-btn-view">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                                <a href="{{ route('admin.parishioners.soa', $parishioner) }}" class="action-btn action-btn-gray" title="Statement of Account">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    SOA
                                </a>
                                <a href="{{ route('admin.parishioners.edit', $parishioner) }}" class="action-btn action-btn-edit">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            No parishioners found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($parishioners->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $parishioners->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
// ── Live search dropdown ─────────────────────────────────────────────────────
(function () {
    const inp  = document.getElementById('parishioner-search');
    const drop = document.getElementById('parishioner-dropdown');
    if (!inp) return;
    let timer;

    inp.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { drop.classList.add('hidden'); drop.innerHTML = ''; return; }

        drop.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400 flex items-center gap-2">'
            + '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">'
            + '<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 60"/></svg>'
            + 'Searching…</div>';
        drop.classList.remove('hidden');

        timer = setTimeout(async () => {
            try {
                const res  = await fetch(`{{ route('admin.parishioners.search') }}?q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
                });
                const data = await res.json();
                if (!data.length) {
                    drop.innerHTML = '<div class="px-4 py-3 text-sm text-gray-400">No results found.</div>';
                    return;
                }
                drop.innerHTML = data.map(p => `
                    <a href="${p.url}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-blue-50 transition border-b border-gray-50 last:border-0">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm flex-shrink-0">${p.text[0]}</div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-800 truncate">${p.text}</p>
                            <p class="text-xs text-gray-400">${p.extra || ''}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                `).join('');
            } catch { drop.innerHTML = '<div class="px-4 py-3 text-sm text-red-500">Search error.</div>'; }
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!inp.contains(e.target) && !drop.contains(e.target)) drop.classList.add('hidden');
    });
    inp.addEventListener('focus', () => { if (inp.value.length >= 2) drop.classList.remove('hidden'); });
})();
</script>
@endpush
