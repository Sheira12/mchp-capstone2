@extends('layouts.public')
@section('title', 'Parish Events')

@section('content')
{{-- Hero --}}
<section class="relative bg-gradient-to-br from-indigo-900 via-blue-900 to-blue-800 py-20 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-10 left-10 w-64 h-64 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-yellow-400 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-5xl mx-auto px-4 text-center text-white">
        <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 text-sm font-medium text-blue-200 mb-4">📅 Parish Calendar</span>
        <h1 class="text-4xl font-bold mb-3">Upcoming Events</h1>
        <p class="text-blue-200 text-lg max-w-xl mx-auto">Join us for upcoming parish activities, celebrations, and community gatherings.</p>
    </div>
</section>

{{-- Featured Event --}}
@if($featuredEvent)
<section class="max-w-5xl mx-auto px-4 py-10">
    <div class="rounded-2xl overflow-hidden shadow-lg border border-gray-100 flex flex-col md:flex-row gap-0">
        @if($featuredEvent->image_path)
            <img src="{{ Storage::url($featuredEvent->image_path) }}" class="md:w-64 h-48 md:h-auto object-cover flex-shrink-0">
        @else
            <div class="md:w-64 h-48 md:h-auto bg-gradient-to-br from-indigo-600 to-blue-700 flex items-center justify-center flex-shrink-0">
                <span class="text-white text-5xl">⛪</span>
            </div>
        @endif
        <div class="p-6 flex-1 bg-white">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 mb-3">★ Featured Event</span>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $featuredEvent->title }}</h2>
            <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-3">
                <span>📅 {{ $featuredEvent->event_start->format('F d, Y') }}</span>
                <span>🕐 {{ $featuredEvent->event_start->format('h:i A') }}</span>
                @if($featuredEvent->location)<span>📍 {{ $featuredEvent->location }}</span>@endif
            </div>
            @if($featuredEvent->description)
                <p class="text-gray-600 text-sm leading-relaxed">{{ Str::limit($featuredEvent->description, 200) }}</p>
            @endif
            <a href="{{ route('events.show', $featuredEvent) }}" class="inline-block mt-4 px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">View Details</a>
        </div>
    </div>
</section>
@endif

{{-- Events Grid --}}
<section class="max-w-5xl mx-auto px-4 pb-16">
    <h2 class="text-xl font-bold text-gray-900 mb-6">All Upcoming Events</h2>

    @if($upcomingEvents->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($upcomingEvents as $event)
        <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
            @if($event->image_path)
                <img src="{{ Storage::url($event->image_path) }}" class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-300">
            @else
                <div class="w-full h-44 bg-gradient-to-br from-indigo-100 to-blue-100 flex items-center justify-center">
                    <span class="text-4xl">📅</span>
                </div>
            @endif
            <div class="p-5">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                        {{ $event->category_label }}
                    </span>
                    @if($event->is_featured)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">★</span>
                    @endif
                </div>
                <h3 class="font-semibold text-gray-900 mb-1 leading-snug">{{ $event->title }}</h3>
                <p class="text-xs text-gray-500 mb-1">📅 {{ $event->event_start->format('M d, Y · h:i A') }}</p>
                @if($event->location)
                    <p class="text-xs text-gray-500 mb-3">📍 {{ $event->location }}</p>
                @endif
                @if($event->description)
                    <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">{{ Str::limit($event->description, 100) }}</p>
                @endif
                <a href="{{ route('events.show', $event) }}"
                   class="inline-block mt-3 text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                    Read more →
                </a>
            </div>
        </article>
        @endforeach
    </div>

    <div class="mt-8">{{ $upcomingEvents->links() }}</div>
    @else
    <div class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-4">📅</div>
        <p class="text-lg font-medium">No upcoming events at this time.</p>
        <p class="text-sm mt-1">Check back soon for parish activities and celebrations.</p>
    </div>
    @endif
</section>

{{-- Past Events --}}
@if(isset($pastEvents) && $pastEvents->count())
<section class="max-w-5xl mx-auto px-4 pb-16">
    <div class="flex items-center gap-3 mb-6">
        <h2 class="text-xl font-bold text-gray-900">Past Events</h2>
        <span class="h-px flex-1 bg-gray-200"></span>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($pastEvents as $event)
        <article class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group opacity-80 hover:opacity-100">
            @if($event->image_path)
                <div class="relative">
                    <img src="{{ Storage::url($event->image_path) }}" class="w-full h-40 object-cover grayscale group-hover:grayscale-0 transition duration-300">
                    <div class="absolute top-2 left-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-800/70 text-white">Past</span>
                    </div>
                </div>
            @else
                <div class="w-full h-40 bg-gray-100 flex items-center justify-center relative">
                    <span class="text-3xl opacity-40">📅</span>
                    <div class="absolute top-2 left-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-400/70 text-white">Past</span>
                    </div>
                </div>
            @endif
            <div class="p-4">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 mb-2">
                    {{ $event->category_label }}
                </span>
                <h3 class="font-semibold text-gray-700 mb-1 leading-snug text-sm">{{ $event->title }}</h3>
                <p class="text-xs text-gray-400">📅 {{ $event->event_start->format('M d, Y') }}</p>
                @if($event->location)
                    <p class="text-xs text-gray-400">📍 {{ $event->location }}</p>
                @endif
                <a href="{{ route('events.show', $event) }}"
                   class="inline-block mt-2 text-xs font-medium text-gray-500 hover:text-indigo-600 hover:underline">
                    View details →
                </a>
            </div>
        </article>
        @endforeach
    </div>
</section>
@endif
@endsection
