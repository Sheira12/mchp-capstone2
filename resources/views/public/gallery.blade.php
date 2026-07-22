@extends('layouts.public')
@section('title', 'Parish Gallery')
@section('meta-description', 'Photo gallery of Mary Help of Christians Parish — Masses, sacraments, community events and more.')

@section('content')

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-blue-900 via-indigo-900 to-blue-800 py-14 overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-400 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-400 rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-5xl mx-auto px-4 text-center text-white">
        <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 text-sm font-medium text-blue-200 mb-4">📷 Parish Life</span>
        <h1 class="text-4xl font-bold mb-3">Photo Gallery</h1>
        <p class="text-blue-200 max-w-xl mx-auto">Celebrating faith, community, and the sacraments of Mary Help of Christians Parish.</p>
    </div>
</section>

<section class="max-w-6xl mx-auto px-4 py-8">

    {{-- ── VIEW MODE TABS: Albums | All Photos ── --}}
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('gallery') }}"
               class="px-4 py-2 rounded-full text-sm font-semibold transition {{ !$album && !$category ? 'bg-blue-600 text-white shadow' : 'bg-white border border-gray-200 text-gray-600 hover:border-blue-300' }}">
                🖼 All Photos
            </a>
            @foreach($albums as $alb)
            <a href="{{ route('gallery', ['album' => $alb->album]) }}"
               class="px-4 py-2 rounded-full text-sm font-semibold transition flex items-center gap-1.5 {{ $album === $alb->album ? 'bg-blue-600 text-white shadow' : 'bg-white border border-gray-200 text-gray-600 hover:border-blue-300' }}">
                📁 {{ $alb->album }}
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs {{ $album === $alb->album ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $alb->total }}</span>
            </a>
            @endforeach
        </div>

        {{-- Category filter (only shown when not in an album) --}}
        @if(!$album)
        <div class="flex gap-2 flex-wrap">
            @foreach($categories as $key => $label)
            <a href="{{ route('gallery', ['category' => $key]) }}"
               class="px-3 py-1.5 rounded-full text-xs font-semibold transition {{ $category === $key ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Album header when viewing a specific album --}}
    @if($album)
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl px-6 py-4 mb-6 flex items-center gap-3">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0 text-xl">📁</div>
        <div>
            <h2 class="font-bold text-lg">{{ $album }}</h2>
            <p class="text-blue-200 text-sm">{{ $items->total() }} photo(s) in this album</p>
        </div>
        <a href="{{ route('gallery') }}" class="ml-auto text-blue-200 hover:text-white text-sm flex items-center gap-1">
            ← All photos
        </a>
    </div>
    @endif

    {{-- ── PHOTO GRID ── --}}
    @if($items->count())
    <div class="columns-2 sm:columns-3 md:columns-4 gap-3 space-y-3">
        @foreach($items as $item)
        <div class="break-inside-avoid group relative rounded-xl overflow-hidden bg-gray-100 cursor-pointer"
             onclick="openLightbox(
                 '{{ Storage::url($item->image_path) }}',
                 '{{ addslashes($item->title ?? '') }}',
                 '{{ addslashes($item->caption ?? '') }}',
                 '{{ addslashes($item->album ?? '') }}',
                 '{{ addslashes($item->category_label) }}'
             )">
            <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}"
                 class="w-full block transition group-hover:scale-105 duration-300"
                 loading="lazy">

            {{-- Hover overlay --}}
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent p-3
                        translate-y-full group-hover:translate-y-0 transition-transform duration-200">
                @if($item->title)
                <p class="text-white text-sm font-semibold leading-tight">{{ $item->title }}</p>
                @endif
                @if($item->caption)
                <p class="text-white/75 text-xs mt-0.5 line-clamp-2">{{ $item->caption }}</p>
                @endif
                @if($item->album)
                <span class="inline-block mt-1 px-2 py-0.5 bg-blue-500/80 text-white text-xs rounded-full">📁 {{ $item->album }}</span>
                @endif
            </div>

            {{-- Badges --}}
            @if($item->album_cover)
            <span class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-1.5 py-0.5 rounded-full shadow">★ Cover</span>
            @elseif($item->is_featured)
            <span class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-1.5 py-0.5 rounded-full shadow">★</span>
            @endif

            {{-- Expand icon on hover --}}
            <div class="absolute top-2 right-2 w-8 h-8 bg-black/50 rounded-full flex items-center justify-center
                        opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                </svg>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8">{{ $items->links() }}</div>

    @else
    <div class="text-center py-20 text-gray-400">
        <div class="text-5xl mb-4">📷</div>
        <p class="text-lg font-medium text-gray-500">
            @if($album) No photos in album "{{ $album }}" yet.
            @elseif($category) No photos in this category yet.
            @else No photos yet.
            @endif
        </p>
        <p class="text-sm mt-1">Check back soon for parish photos.</p>
        @if($album || $category)
        <a href="{{ route('gallery') }}" class="inline-block mt-4 text-blue-600 hover:underline text-sm">← View all photos</a>
        @endif
    </div>
    @endif
</section>

{{-- ── LIGHTBOX ── --}}
<div id="lightbox" class="fixed inset-0 bg-black/95 z-50 hidden items-center justify-center p-4"
     onclick="if(event.target===this)closeLightbox()">

    {{-- Close button --}}
    <button onclick="closeLightbox()"
            class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white text-xl transition">
        ✕
    </button>

    {{-- Prev / Next --}}
    <button id="lb-prev" onclick="lbNavigate(-1)"
            class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white text-xl transition">
        ‹
    </button>
    <button id="lb-next" onclick="lbNavigate(1)"
            class="absolute right-16 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white text-xl transition">
        ›
    </button>

    <div class="max-w-4xl w-full flex flex-col items-center">
        <img id="lb-img" src="" alt="" class="max-h-[80vh] max-w-full object-contain rounded-xl shadow-2xl">
        <div id="lb-info" class="text-center mt-4 space-y-1">
            <div id="lb-title" class="text-white font-semibold text-lg hidden"></div>
            <div id="lb-cap" class="text-white/70 text-sm hidden"></div>
            <div class="flex items-center justify-center gap-2 mt-1">
                <div id="lb-album" class="text-blue-300 text-xs hidden"></div>
                <div id="lb-cat" class="text-gray-400 text-xs hidden"></div>
                <div id="lb-counter" class="text-gray-500 text-xs"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Collect all photos for lightbox navigation
const lbPhotos = [
    @foreach($items as $item)
    {
        src:     '{{ Storage::url($item->image_path) }}',
        title:   '{{ addslashes($item->title ?? '') }}',
        caption: '{{ addslashes($item->caption ?? '') }}',
        album:   '{{ addslashes($item->album ?? '') }}',
        cat:     '{{ addslashes($item->category_label) }}'
    },
    @endforeach
];
let lbCurrent = 0;

function openLightbox(src, title, caption, album, cat) {
    lbCurrent = lbPhotos.findIndex(p => p.src === src);
    renderLightbox();
    document.getElementById('lightbox').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function renderLightbox() {
    const p = lbPhotos[lbCurrent];
    document.getElementById('lb-img').src    = p.src;
    const titleEl = document.getElementById('lb-title');
    const capEl   = document.getElementById('lb-cap');
    const albumEl = document.getElementById('lb-album');
    const catEl   = document.getElementById('lb-cat');
    const ctrEl   = document.getElementById('lb-counter');

    titleEl.textContent = p.title;  titleEl.classList.toggle('hidden', !p.title);
    capEl.textContent   = p.caption; capEl.classList.toggle('hidden', !p.caption);
    albumEl.textContent = p.album ? '📁 ' + p.album : ''; albumEl.classList.toggle('hidden', !p.album);
    catEl.textContent   = p.cat;   catEl.classList.toggle('hidden', !p.cat);
    ctrEl.textContent   = (lbCurrent + 1) + ' / ' + lbPhotos.length;

    document.getElementById('lb-prev').style.display = lbCurrent > 0 ? 'flex' : 'none';
    document.getElementById('lb-next').style.display = lbCurrent < lbPhotos.length - 1 ? 'flex' : 'none';
}

function lbNavigate(dir) {
    lbCurrent = Math.max(0, Math.min(lbPhotos.length - 1, lbCurrent + dir));
    renderLightbox();
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (document.getElementById('lightbox').style.display !== 'flex') return;
    if (e.key === 'Escape')     closeLightbox();
    if (e.key === 'ArrowLeft')  lbNavigate(-1);
    if (e.key === 'ArrowRight') lbNavigate(1);
});
</script>
@endsection
