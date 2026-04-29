@extends('layouts.public')

@section('title', 'Announcements')
@section('meta-description', 'Latest announcements and news from Mary Help of Christians Parish')

@section('content')

{{-- Page Header --}}
<section class="bg-gradient-to-br from-blue-900 to-indigo-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-bold mb-3">Parish Announcements</h1>
        <p class="text-blue-200 text-lg">Stay updated with the latest news and events</p>
    </div>
</section>

{{-- Announcements Grid --}}
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($announcements->isEmpty())
        <div class="text-center py-16">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            <p class="text-gray-500 text-lg">No announcements at this time.</p>
            <p class="text-gray-400 text-sm mt-1">Check back later for updates.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($announcements as $announcement)
            <a href="{{ route('announcements.show', $announcement) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:border-blue-200 transition group">
                @if($announcement->image_path)
                <img src="{{ Storage::url($announcement->image_path) }}" alt="{{ $announcement->title }}" class="w-full h-48 object-cover">
                @else
                <div class="w-full h-48 bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                    <svg class="w-16 h-16 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                @endif
                <div class="p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 uppercase tracking-wide">
                            {{ $announcement->category }}
                        </span>
                        <span class="text-xs text-gray-400">
                            {{ $announcement->published_at?->format('M d, Y') }}
                        </span>
                    </div>
                    <h3 class="font-bold text-gray-900 text-lg group-hover:text-blue-700 line-clamp-2 mb-2">
                        {{ $announcement->title }}
                    </h3>
                    <p class="text-sm text-gray-600 line-clamp-3">
                        {{ strip_tags($announcement->content) }}
                    </p>
                    <div class="mt-4 flex items-center text-blue-600 text-sm font-medium group-hover:underline">
                        Read more
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-10">
            {{ $announcements->links() }}
        </div>
        @endif
    </div>
</section>

@endsection
