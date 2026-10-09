@extends('layouts.app')
@section('title', 'Announcements')
@section('page-title', 'Announcements')

@section('content')
<div class="py-6 space-y-4">

    {{-- ── Header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Announcements</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $announcements->total() }} total &mdash; manage, schedule, and publish parish news.</p>
        </div>
        <a href="{{ route('admin.announcements.create') }}" class="btn-primary text-sm">+ New Announcement</a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('warning'))
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 text-sm font-medium">{{ session('warning') }}</div>
    @endif

    {{-- ── Search + Filter bar ── --}}
    <form method="GET" id="filter-form" class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title…"
                       class="form-input w-full text-sm">
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-full text-sm">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Announcement::STATUSES as $val => $lbl)
                    <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="category" class="form-select w-full text-sm">
                    <option value="">All Categories</option>
                    @foreach(\App\Models\Announcement::CATEGORIES as $val => $lbl)
                    <option value="{{ $val }}" @selected(request('category') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary text-sm flex-1">Filter</button>
                <a href="{{ route('admin.announcements.index') }}" class="btn-secondary text-sm">Clear</a>
            </div>
        </div>
    </form>

    {{-- ── Bulk action form wraps entire table ── --}}
    <form method="POST" action="{{ route('admin.announcements.bulk') }}" id="bulk-form">
        @csrf

        {{-- Bulk action bar (appears when something is checked) ──--}}
        <div id="bulk-bar"
             class="hidden bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex flex-wrap items-center gap-3 text-sm">
            <span class="font-semibold text-blue-800"><span id="bulk-count">0</span> selected</span>
            <button type="submit" name="action" value="publish"
                    class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1.5 rounded-lg transition text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                Publish Selected
            </button>
            <button type="submit" name="action" value="unpublish"
                    class="inline-flex items-center gap-1.5 bg-gray-600 hover:bg-gray-700 text-white font-semibold px-3 py-1.5 rounded-lg transition text-xs">
                Unpublish Selected
            </button>
            <button type="submit" name="action" value="delete"
                    onclick="return confirm('Delete the selected announcements? This cannot be undone.')"
                    class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-1.5 rounded-lg transition text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete Selected
            </button>
            <button type="button" onclick="clearSelection()" class="text-blue-600 hover:underline text-xs ml-auto">Clear selection</button>
        </div>

        {{-- ── MOBILE CARDS ── --}}
        <div class="space-y-3 lg:hidden mt-4">
            @forelse($announcements as $ann)
            @php [$catClass, $sc, $statusLabel] = annMeta($ann); @endphp
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                <div class="flex items-start gap-3 mb-2">
                    <input type="checkbox" name="ids[]" value="{{ $ann->id }}" class="bulk-cb mt-1 rounded border-gray-300 text-blue-600">
                    @if($ann->image_path)
                    <img src="{{ media_url($ann->image_path) }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
                    @else
                    <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0 text-lg">📢</div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-gray-900 text-sm leading-tight">
                                {{ $ann->title }}
                                @if($ann->is_pinned)<span class="text-amber-500 ml-1">📌</span>@endif
                            </p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-800 shrink-0">{{ $statusLabel }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $catClass }}">{{ ucfirst($ann->category) }}</span>
                            @if($ann->published_at)
                            <span class="text-xs text-gray-400">{{ $ann->published_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                        @if($ann->expires_at && $ann->expires_at->isPast())
                        <span class="text-xs text-red-500 font-semibold">⚠ Expired</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-gray-100">
                    <a href="{{ route('admin.announcements.edit', $ann) }}" class="action-btn action-btn-edit">Edit</a>
                    @if($ann->status === 'published')
                    <a href="{{ route('announcements.show', $ann) }}" target="_blank" class="action-btn action-btn-view text-xs">Preview</a>
                    @endif
                    <form method="POST" action="{{ route('admin.announcements.destroy', $ann) }}" class="inline" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn action-btn-delete">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-400">No announcements match your filters.</div>
            @endforelse
            @if($announcements->hasPages())
            <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">{{ $announcements->withQueryString()->links() }}</div>
            @endif
        </div>

        {{-- ── DESKTOP TABLE ── --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block mt-4">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-4 py-3 font-medium w-8">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-blue-600"
                                   title="Select all">
                        </th>
                        <th class="px-4 py-3 font-medium">Title</th>
                        <th class="px-4 py-3 font-medium">Category</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Published / Scheduled</th>
                        <th class="px-4 py-3 font-medium">Expires</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($announcements as $ann)
                    @php [$catClass, $sc, $statusLabel] = annMeta($ann); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $ann->id }}"
                                   class="bulk-cb rounded border-gray-300 text-blue-600">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($ann->image_path)
                                <img src="{{ media_url($ann->image_path) }}" class="w-10 h-10 rounded object-cover flex-shrink-0"
                                     onerror="this.style.display='none'">
                                @endif
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ $ann->title }}
                                        @if($ann->is_pinned)<span class="text-amber-400 ml-1" title="Pinned">📌</span>@endif
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">by {{ $ann->createdBy?->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $catClass }}">{{ ucfirst($ann->category) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-800">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                            {{ $ann->published_at ? $ann->published_at->format('M d, Y') : ($ann->scheduled_at ? '🕐 '.$ann->scheduled_at->format('M d, Y g:ia') : '—') }}
                        </td>
                        <td class="px-4 py-3 text-xs whitespace-nowrap">
                            @if($ann->expires_at)
                            <span class="{{ $ann->expires_at->isPast() ? 'text-red-500 font-semibold' : 'text-gray-500' }}">
                                {{ $ann->expires_at->format('M d, Y') }}
                                @if($ann->expires_at->isPast())<span class="text-red-400"> (expired)</span>@endif
                            </span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <a href="{{ route('admin.announcements.edit', $ann) }}" class="action-btn action-btn-edit">Edit</a>
                                @if($ann->status === 'published')
                                <a href="{{ route('announcements.show', $ann) }}" target="_blank"
                                   class="action-btn action-btn-view text-xs" title="View on public site">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    Preview
                                </a>
                                @endif
                                <form method="POST" action="{{ route('admin.announcements.destroy', $ann) }}"
                                      class="inline" onsubmit="return confirm('Delete this announcement?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No announcements match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            @if($announcements->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $announcements->withQueryString()->links() }}</div>
            @endif
        </div>
    </form>

</div>

@php
function annMeta($ann): array {
    $categoryBg = ['general'=>'bg-blue-100 text-blue-800','event'=>'bg-purple-100 text-purple-800','mass'=>'bg-amber-100 text-amber-800','sacrament'=>'bg-rose-100 text-rose-800','notice'=>'bg-teal-100 text-teal-800'];
    $catClass   = $categoryBg[$ann->category ?? 'general'] ?? 'bg-blue-100 text-blue-800';
    $sc         = ['published'=>'green','scheduled'=>'blue','draft'=>'gray'][$ann->status ?? 'draft'] ?? 'gray';
    $label      = ['published'=>'Published','scheduled'=>'Scheduled','draft'=>'Draft'][$ann->status ?? 'draft'] ?? 'Draft';
    return [$catClass, $sc, $label];
}
@endphp

@push('scripts')
<script>
const cbs       = () => document.querySelectorAll('.bulk-cb');
const bulkBar   = document.getElementById('bulk-bar');
const countEl   = document.getElementById('bulk-count');
const selectAll = document.getElementById('select-all');

function updateBulkBar() {
    const checked = [...cbs()].filter(c => c.checked).length;
    countEl.textContent = checked;
    bulkBar.classList.toggle('hidden', checked === 0);
}

function clearSelection() {
    cbs().forEach(c => c.checked = false);
    if (selectAll) selectAll.checked = false;
    updateBulkBar();
}

cbs().forEach(c => c.addEventListener('change', updateBulkBar));

if (selectAll) {
    selectAll.addEventListener('change', function () {
        cbs().forEach(c => c.checked = this.checked);
        updateBulkBar();
    });
}
</script>
@endpush
@endsection
