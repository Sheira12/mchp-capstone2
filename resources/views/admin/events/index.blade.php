@extends('layouts.app')
@section('title', 'Events')
@section('page-title', 'Parish Events')

@section('content')
<div class="py-6 space-y-4">

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search events..."
               class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-56 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="category" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Categories</option>
            @foreach(\App\Models\Event::CATEGORIES as $key => $label)
                <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <button type="submit" class="action-btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.events.index') }}" class="action-btn btn-ghost btn-sm">Reset</a>
        <div class="ml-auto">
            <a href="{{ route('admin.events.create') }}" class="action-btn btn-primary">+ New Event</a>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($event->image_path)
                                <img src="{{ Storage::url($event->image_path) }}" class="w-10 h-10 rounded object-cover flex-shrink-0">
                            @else
                                <div class="w-10 h-10 rounded bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-indigo-600 text-lg">📅</span>
                                </div>
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
                    <td class="px-4 py-3 text-gray-600">
                        <div>{{ $event->event_start->format('M d, Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $event->event_start->format('h:i A') }}
                            @if($event->event_end) – {{ $event->event_end->format('h:i A') }} @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $event->location ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @php
                            $badge = match($event->status) {
                                'published'  => 'bg-green-100 text-green-800',
                                'draft'      => 'bg-gray-100 text-gray-600',
                                'cancelled'  => 'bg-red-100 text-red-700',
                                default      => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                            {{ ucfirst($event->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('admin.events.show', $event) }}" class="action-btn btn-ghost btn-sm">View</a>
                            <a href="{{ route('admin.events.edit', $event) }}" class="action-btn btn-warning btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete this event?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No events found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($events->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $events->links() }}</div>
        @endif
    </div>
</div>
@endsection
