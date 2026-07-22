@extends('layouts.app')
@section('title', $event->title)
@section('page-title', 'Event Details')

@section('content')
<div class="py-6 max-w-3xl space-y-5">
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.events.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Events</a>
        <div class="flex gap-2">
            <a href="{{ route('admin.events.edit', $event) }}" class="action-btn btn-warning btn-sm">Edit</a>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" onsubmit="return confirm('Delete this event?')">
                @csrf @method('DELETE')
                <button type="submit" class="action-btn btn-danger btn-sm">Delete</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($event->image_path)
            <img src="{{ Storage::url($event->image_path) }}" class="w-full h-48 object-cover">
        @endif
        <div class="p-6 space-y-4">
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-xl font-bold text-gray-900">{{ $event->title }}</h2>
                @if($event->is_featured)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">★ Featured</span>
                @endif
                @php
                    $badge = match($event->status) {
                        'published' => 'bg-green-100 text-green-800',
                        'draft'     => 'bg-gray-100 text-gray-600',
                        'cancelled' => 'bg-red-100 text-red-700',
                        default     => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ ucfirst($event->status) }}</span>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 mb-1">Category</p>
                    <p class="font-medium">{{ $event->category_label }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Location</p>
                    <p class="font-medium">{{ $event->location ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Start</p>
                    <p class="font-medium">{{ $event->event_start->format('F d, Y — h:i A') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">End</p>
                    <p class="font-medium">{{ $event->event_end?->format('F d, Y — h:i A') ?? 'Not specified' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Created by</p>
                    <p class="font-medium">{{ $event->creator?->name ?? 'System' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-1">Created at</p>
                    <p class="font-medium">{{ $event->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            @if($event->description)
            <div>
                <p class="text-gray-500 text-sm mb-2">Description</p>
                <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap bg-gray-50 rounded-lg p-4">{{ $event->description }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
