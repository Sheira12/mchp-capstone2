@extends('layouts.app')
@section('title', 'Announcements')
@section('page-title', 'Announcements')

@section('content')
<div class="py-6 space-y-4">

    <div class="flex justify-end">
        <a href="{{ route('admin.announcements.create') }}" class="btn-primary text-sm">+ New Announcement</a>
    </div>

    {{-- ── MOBILE CARDS ── --}}
    <div class="space-y-3 lg:hidden">
        @forelse($announcements as $ann)
        @php
            $categoryColors = ['general'=>'blue','event'=>'purple','mass'=>'amber','sacrament'=>'rose','announcement'=>'teal','news'=>'indigo','urgent'=>'red','reminder'=>'orange'];
            $catKey   = strtolower($ann->category ?? 'general');
            $catColor = $categoryColors[$catKey] ?? 'blue';
            $categoryBg = ['blue'=>'bg-blue-100 text-blue-800','purple'=>'bg-purple-100 text-purple-800','amber'=>'bg-amber-100 text-amber-800','rose'=>'bg-rose-100 text-rose-800','teal'=>'bg-teal-100 text-teal-800','indigo'=>'bg-indigo-100 text-indigo-800','red'=>'bg-red-100 text-red-800','orange'=>'bg-orange-100 text-orange-800'];
            $catClass = $categoryBg[$catColor] ?? 'bg-blue-100 text-blue-800';
        @endphp
        <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
            <div class="flex items-start gap-3 mb-2">
                @if($ann->image_path)
                <img src="{{ Storage::url($ann->image_path) }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
                @else
                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0 text-lg">📢</div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-semibold text-gray-900 text-sm leading-tight">{{ $ann->title }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $ann->published_at ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }} flex-shrink-0">
                            {{ $ann->published_at ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $catClass }}">
                            {{ ucfirst($ann->category) }}
                        </span>
                        @if($ann->published_at)
                        <span class="text-xs text-gray-400">{{ $ann->published_at->format('M d, Y') }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-1">by {{ $ann->createdBy?->name ?? '—' }}</p>
                    @if($ann->expires_at && $ann->expires_at->isPast())
                    <span class="text-xs text-red-500 font-semibold">⚠ Expired {{ $ann->expires_at->format('M d, Y') }}</span>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-gray-100">
                <a href="{{ route('admin.announcements.edit', $ann) }}" class="action-btn action-btn-edit">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <form method="POST" action="{{ route('admin.announcements.destroy', $ann) }}" class="inline"
                      onsubmit="return confirm('Delete this announcement?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn action-btn-delete">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-400">No announcements yet.</div>
        @endforelse
        @if($announcements->hasPages())
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">{{ $announcements->links() }}</div>
        @endif
    </div>

    {{-- ── DESKTOP TABLE ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Title</th>
                    <th class="px-4 py-3 font-medium">Category</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Published</th>
                    <th class="px-4 py-3 font-medium">Expires</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($announcements as $ann)
                @php
                    $categoryColors = ['general'=>'blue','event'=>'purple','mass'=>'amber','sacrament'=>'rose','announcement'=>'teal','news'=>'indigo','urgent'=>'red','reminder'=>'orange'];
                    $catKey   = strtolower($ann->category ?? 'general');
                    $catColor = $categoryColors[$catKey] ?? 'blue';
                    $categoryBg = ['blue'=>'bg-blue-100 text-blue-800','purple'=>'bg-purple-100 text-purple-800','amber'=>'bg-amber-100 text-amber-800','rose'=>'bg-rose-100 text-rose-800','teal'=>'bg-teal-100 text-teal-800','indigo'=>'bg-indigo-100 text-indigo-800','red'=>'bg-red-100 text-red-800','orange'=>'bg-orange-100 text-orange-800'];
                    $catClass = $categoryBg[$catColor] ?? 'bg-blue-100 text-blue-800';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($ann->image_path)
                            <img src="{{ Storage::url($ann->image_path) }}" class="w-10 h-10 rounded object-cover flex-shrink-0">
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $ann->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">by {{ $ann->createdBy?->name ?? '—' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $catClass }}">
                            {{ ucfirst($ann->category) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($ann->published_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $ann->published_at ? $ann->published_at->format('M d, Y') : '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        @if($ann->expires_at)
                        <span class="{{ $ann->expires_at->isPast() ? 'text-red-500 font-semibold' : '' }}">
                            {{ $ann->expires_at->format('M d, Y') }}
                            @if($ann->expires_at->isPast())<span class="ml-1 text-red-400">(expired)</span>@endif
                        </span>
                        @else —
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="{{ route('admin.announcements.edit', $ann) }}" class="action-btn action-btn-edit">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.announcements.destroy', $ann) }}" class="inline" onsubmit="return confirm('Delete this announcement?')">
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
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No announcements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($announcements->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $announcements->links() }}</div>
        @endif
    </div>
</div>
@endsection
