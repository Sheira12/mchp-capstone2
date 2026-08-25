@extends('layouts.app')
@section('title', 'Families')
@section('page-title', 'Families')

@section('content')
<div class="py-6 space-y-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="flex gap-2 flex-1">
            <div class="relative flex-1 max-w-sm">
                <input type="text" id="family-search" name="search" value="{{ request('search') }}"
                       placeholder="Type 2+ chars to search families…"
                       class="form-input text-sm w-full" autocomplete="off">
                <div id="family-dropdown"
                     class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-xl z-40 hidden mt-1 max-h-64 overflow-y-auto"></div>
            </div>
            <button type="submit" class="btn-secondary text-sm">Search</button>
            @if(request('search'))
            <a href="{{ route('admin.families.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
        </form>
        <a href="{{ route('admin.families.create') }}" class="btn-primary text-sm whitespace-nowrap">+ New Family</a>
    </div>

    {{-- ── MOBILE CARDS ── --}}
    <div class="space-y-3 lg:hidden">
        @forelse($families as $family)
        <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
            <div class="flex items-start gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0">
                    {{ substr($family->family_name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('admin.families.show', $family) }}" class="font-semibold text-gray-900 hover:text-blue-700 text-sm">
                        {{ $family->family_name }}
                    </a>
                    @if($family->barangay || $family->address)
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ implode(', ', array_filter([$family->address, $family->barangay])) }}
                    </p>
                    @endif
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $family->members_count }} member{{ $family->members_count != 1 ? 's' : '' }}
                        </span>
                        @if($family->contact_number)
                        <span class="text-xs text-gray-500">📞 {{ $family->contact_number }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-gray-100">
                <a href="{{ route('admin.families.show', $family) }}" class="action-btn action-btn-view">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    View
                </a>
                <a href="{{ route('admin.families.edit', $family) }}" class="action-btn action-btn-edit">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('admin.families.destroy', $family) }}" class="inline"
                      onsubmit="return confirm('Delete this family? Members will be unlinked.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn action-btn-delete">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-400">No families found.</div>
        @endforelse
        @if($families->hasPages())
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">{{ $families->links() }}</div>
        @endif
    </div>

    {{-- ── DESKTOP TABLE ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Family Name</th>
                    <th class="px-4 py-3 font-medium">Address</th>
                    <th class="px-4 py-3 font-medium">Barangay</th>
                    <th class="px-4 py-3 font-medium">Members</th>
                    <th class="px-4 py-3 font-medium">Contact</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($families as $family)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('admin.families.show', $family) }}" class="hover:text-blue-700">{{ $family->family_name }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $family->address ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $family->barangay ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $family->members_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $family->contact_number ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="{{ route('admin.families.show', $family) }}" class="action-btn action-btn-view">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                            <a href="{{ route('admin.families.edit', $family) }}" class="action-btn action-btn-edit">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.families.destroy', $family) }}" class="inline"
                                  onsubmit="return confirm('Delete this family? Members will be unlinked.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No families found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $families->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const inp  = document.getElementById('family-search');
    const drop = document.getElementById('family-dropdown');
    if (!inp) return;
    let timer;

    inp.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { drop.classList.add('hidden'); drop.innerHTML = ''; return; }
        drop.innerHTML = '<div class="px-4 py-3 text-xs text-gray-400 flex items-center gap-2"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 60"/></svg>Searching…</div>';
        drop.classList.remove('hidden');
        timer = setTimeout(async () => {
            try {
                const res  = await fetch(`{{ route('admin.families.search') }}?q=${encodeURIComponent(q)}`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } });
                const data = await res.json();
                if (!data.length) { drop.innerHTML = '<div class="px-4 py-3 text-sm text-gray-400">No families found.</div>'; return; }
                drop.innerHTML = data.map(f => `<a href="${f.url}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-blue-50 transition border-b border-gray-50 last:border-0"><div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm flex-shrink-0">${f.text[0]}</div><div class="min-w-0"><p class="text-sm font-semibold text-gray-800">${f.text}</p><p class="text-xs text-gray-400">${f.extra || ''}</p></div><svg class="w-4 h-4 text-gray-300 ml-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>`).join('');
            } catch { drop.innerHTML = '<div class="px-4 py-3 text-sm text-red-500">Search error.</div>'; }
        }, 300);
    });
    document.addEventListener('click', e => { if (!inp.contains(e.target) && !drop.contains(e.target)) drop.classList.add('hidden'); });
    inp.addEventListener('focus', () => { if (inp.value.length >= 2) drop.classList.remove('hidden'); });
})();
</script>
@endpush
