@extends('layouts.public')
@section('title', 'Livestream & Videos')
@section('meta-description', 'Watch live and recorded Masses, sacraments, and events from Mary Help of Christians Parish.')

@section('content')
{{-- Hero --}}
<section class="relative bg-gradient-to-br from-gray-900 via-blue-950 to-gray-900 py-16 overflow-hidden">
    <div class="absolute inset-0 opacity-5">
        <svg width="100%" height="100%"><defs><pattern id="g" width="60" height="60" patternUnits="userSpaceOnUse"><circle cx="30" cy="30" r="1.5" fill="#fff"/></pattern></defs><rect width="100%" height="100%" fill="url(#g)"/></svg>
    </div>
    <div class="relative max-w-5xl mx-auto px-4 text-center text-white">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-red-600/20 border border-red-500/30 text-red-300 text-sm font-semibold mb-4">
            <span class="w-2 h-2 bg-red-400 rounded-full animate-pulse"></span> LIVE & ON-DEMAND
        </div>
        <h1 class="text-4xl font-bold mb-3">Parish Livestream</h1>
        <p class="text-gray-300 max-w-xl mx-auto">Watch live Masses, sacraments, and parish events from anywhere.</p>
    </div>
</section>

<section class="max-w-5xl mx-auto px-4 py-10 space-y-10">

    {{-- Featured / Latest --}}
    @if($featured)
    <div>
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
            @if($featured->type === 'live')
                <span class="flex items-center gap-1.5 text-red-600"><span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span> Live Now</span>
            @else
                <span class="text-yellow-600">★ Featured</span>
            @endif
        </h2>
        <div class="rounded-2xl overflow-hidden shadow-xl bg-gray-900">
            <div class="aspect-video">
                <iframe src="{{ $featured->embed_url }}&autoplay=0" class="w-full h-full" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>
            <div class="p-5 bg-gray-900 text-white">
                <h3 class="text-lg font-bold">{{ $featured->title }}</h3>
                @if($featured->description)<p class="text-gray-300 text-sm mt-1">{{ $featured->description }}</p>@endif
                @if($featured->scheduled_at)<p class="text-gray-500 text-xs mt-2">📅 {{ $featured->scheduled_at->format('F d, Y · h:i A') }}</p>@endif
            </div>
        </div>
    </div>
    @endif

    {{-- All Videos Grid --}}
    @if($livestreams->count())
    <div>
        <h2 class="text-xl font-bold text-gray-900 mb-5">All Videos</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($livestreams as $ls)
            <article class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-md transition group">
                <div class="relative aspect-video bg-gray-900 cursor-pointer" onclick="playVideo('{{ $ls->youtube_id ?? \App\Models\Livestream::extractYoutubeId($ls->youtube_url) }}', this)">
                    <img src="{{ $ls->thumbnail }}" alt="{{ $ls->title }}"
                         class="w-full h-full object-cover opacity-90 group-hover:opacity-70 transition"
                         onerror="this.style.display='none'">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-14 h-14 rounded-full bg-red-600/90 flex items-center justify-center shadow-xl group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                    @if($ls->type === 'live')
                        <span class="absolute top-2 left-2 flex items-center gap-1 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded">
                            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span> LIVE
                        </span>
                    @elseif($ls->type === 'upcoming')
                        <span class="absolute top-2 left-2 bg-amber-500 text-white text-xs font-bold px-2 py-1 rounded">UPCOMING</span>
                    @endif
                    <div class="video-container absolute inset-0 hidden">
                        <iframe class="w-full h-full" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 text-sm leading-snug">{{ $ls->title }}</h3>
                    @if($ls->scheduled_at)
                        <p class="text-xs text-gray-400 mt-1">📅 {{ $ls->scheduled_at->format('M d, Y') }}</p>
                    @endif
                    @if($ls->description)
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $ls->description }}</p>
                    @endif
                    <a href="{{ $ls->youtube_url }}" target="_blank"
                       class="inline-flex items-center gap-1 mt-2 text-xs text-red-600 font-semibold hover:underline">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        Watch on YouTube
                    </a>
                </div>
            </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $livestreams->links() }}</div>
    </div>
    @else
    <div class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-4">📺</div>
        <p class="text-lg font-medium">No videos available yet.</p>
        <p class="text-sm mt-1">Live Masses and events will be posted here soon.</p>
    </div>
    @endif

</section>

<script>
function playVideo(id, el) {
    const container = el.querySelector('.video-container');
    if (!container) return;
    container.classList.remove('hidden');
    container.querySelector('iframe').src = `https://www.youtube.com/embed/${id}?autoplay=1&rel=0`;
    el.querySelectorAll('img, div:not(.video-container)').forEach(c => c.classList.add('hidden'));
}
</script>
@endsection
