@extends('layouts.app')
@section('title', 'Events')
@section('page-title', 'Parish Events')

@section('content')
<div class="py-6 space-y-4">

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-0" style="min-width:180px;">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search events…"
                       class="form-input text-sm w-full">
            </div>
            <select name="category" class="form-select text-sm">
                <option value="">All Categories</option>
                @foreach(\App\Models\Event::CATEGORIES as $key => $label)
                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status" class="form-select text-sm">
                <option value="">All Status</option>
                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>Draft</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <a href="{{ route('admin.events.index') }}" class="btn-secondary text-sm">Reset</a>
            <div class="ml-auto">
                <a href="{{ route('admin.events.create') }}" class="btn-primary text-sm">+ New Event</a>
            </div>
        </form>
    </div>

    {{-- ── MOBILE CARDS ── --}}
    <div class="space-y-3 lg:hidden">
        @forelse($events as $event)
        @php
            $badge = match($event->status) {
                'published' => 'bg-green-100 text-green-800',
                'draft'     => 'bg-gray-100 text-gray-600',
                'cancelled' => 'bg-red-100 text-red-700',
                default     => 'bg-gray-100 text-gray-600',
            };
        @endphp
        <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
            <div class="flex items-start gap-3 mb-2">
                @if($event->image_path)
                <img src="{{ Storage::url($event->image_path) }}" class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
                @else
                <div class="w-14 h-14 rounded-lg bg-indigo-50 flex items-center justify-center text-2xl flex-shrink-0">📅</div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 text-sm leading-tight">{{ $event->title }}</p>
                            @if($event->is_featured)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">★ Featured</span>
                            @endif
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }} flex-shrink-0">
                            {{ ucfirst($event->status) }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $event->category_label }}
                        </span>
                        <span class="text-xs text-gray-500">
                            📅 {{ $event->event_start->format('M d, Y') }}
                            · {{ $event->event_start->format('h:i A') }}
                        </span>
                    </div>
                    @if($event->location)
                    <p class="text-xs text-gray-400 mt-1">📍 {{ $event->location }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-gray-100">
                <a href="{{ route('admin.events.show', $event) }}" class="action-btn action-btn-view">View</a>
                <a href="{{ route('admin.events.edit', $event) }}" class="action-btn action-btn-edit">Edit</a>
                <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete this event?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn action-btn-delete">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-400">No events found.</div>
        @endforelse
        @if($events->hasPages())
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">{{ $events->links() }}</div>
        @endif
    </div>

    {{-- ── DESKTOP TABLE ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Event</th>
                    <th class="px-4 py-3 font-medium">Category</th>
                    <th class="px-4 py-3 font-medium">Date &amp; Time</th>
                    <th class="px-4 py-3 font-medium">Location</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($events as $event)
                @php
                    $badge = match($event->status) {
                        'published' => 'bg-green-100 text-green-800',
                        'draft'     => 'bg-gray-100 text-gray-600',
                        'cancelled' => 'bg-red-100 text-red-700',
                        default     => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($event->image_path)
                            <img src="{{ Storage::url($event->image_path) }}" class="w-10 h-10 rounded object-cover flex-shrink-0">
                            @else
                            <div class="w-10 h-10 rounded bg-indigo-50 flex items-center justify-center flex-shrink-0"><span class="text-lg">📅</span></div>
                            @endif
                            <div>
                                <p class="font-medium text-gray-900">{{ $event->title }}</p>
                                @if($event->is_featured)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Featured</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $event->category_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                        <div>{{ $event->event_start->format('M d, Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $event->event_start->format('h:i A') }}@if($event->event_end) – {{ $event->event_end->format('h:i A') }}@endif</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $event->location ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.events.show', $event) }}" class="action-btn action-btn-view">View</a>
                            <a href="{{ route('admin.events.edit', $event) }}" class="action-btn action-btn-edit">Edit</a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete this event?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No events found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($events->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $events->links() }}</div>
        @endif
    </div>
</div>
@endsection
