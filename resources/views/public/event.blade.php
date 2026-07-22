@extends('layouts.public')
@section('title', $event->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <a href="{{ route('events') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline mb-6">← Back to Events</a>

    <article class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if($event->image_path)
            <img src="{{ Storage::url($event->image_path) }}" class="w-full h-64 object-cover">
        @endif
        <div class="p-8">
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700">
                    {{ $event->category_label }}
                </span>
                @if($event->is_featured)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">★ Featured</span>
                @endif
                @if($event->status === 'cancelled')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-700">Cancelled</span>
                @endif
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $event->title }}</h1>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-xl text-sm">
                <div>
                    <p class="text-gray-500 mb-0.5">Date &amp; Time</p>
                    <p class="font-semibold text-gray-800">{{ $event->event_start->format('F d, Y') }}</p>
                    <p class="text-gray-600">{{ $event->event_start->format('h:i A') }}
                        @if($event->event_end) – {{ $event->event_end->format('h:i A') }} @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-500 mb-0.5">Location</p>
                    <p class="font-semibold text-gray-800">{{ $event->location ?? 'To be announced' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 mb-0.5">Parish</p>
                    <p class="font-semibold text-gray-800">{{ config('parish.name') }}</p>
                </div>
            </div>

            @if($event->description)
            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $event->description }}</div>
            @endif
        </div>
    </article>

    {{-- Related events --}}
    @if($relatedEvents->count())
    <div class="mt-10">
        <h2 class="text-xl font-bold text-gray-900 mb-5">More {{ $event->category_label }} Events</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @foreach($relatedEvents as $related)
            <a href="{{ route('events.show', $related) }}"
               class="bg-white rounded-xl border border-gray-100 p-4 hover:shadow-md transition block">
                <p class="font-medium text-gray-900 mb-1">{{ $related->title }}</p>
                <p class="text-xs text-gray-500">📅 {{ $related->event_start->format('M d, Y') }}</p>
                @if($related->location)<p class="text-xs text-gray-500">📍 {{ $related->location }}</p>@endif
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
