@extends('layouts.app')
@section('title', 'Gallery')
@section('page-title', 'Photo Gallery')

@section('content')
<div class="py-6 space-y-4">

    {{-- Top bar --}}
    <div class="flex flex-wrap gap-3 items-center justify-between">
        <div class="flex gap-2">
            <a href="{{ route('admin.gallery.index') }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ !request('album') && !request('category') ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                All Photos
            </a>
            @foreach($albums as $alb)
            <a href="{{ route('admin.gallery.index', ['album' => $alb->album]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-1.5 {{ request('album') === $alb->album ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ $alb->album }}
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs {{ request('album') === $alb->album ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $alb->total }}</span>
            </a>
            @endforeach
        </div>
        <div class="flex gap-2">
            @if(request('album'))
            <a href="{{ route('admin.gallery.album-detail', request('album')) }}"
               class="px-3 py-2 rounded-lg text-sm font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Manage Album
            </a>
            <form method="POST" action="{{ route('admin.gallery.album-delete') }}"
                  onsubmit="return confirm('Delete ALL photos in album &quot;{{ request('album') }}&quot;? This cannot be undone.')">
                @csrf @method('DELETE')
                <input type="hidden" name="album" value="{{ request('album') }}">
                <button type="submit" class="px-3 py-2 rounded-lg text-sm font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Album
                </button>
            </form>
            @endif
            <a href="{{ route('admin.gallery.create') }}"
               class="px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Upload Photos
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    {{-- Album heading when viewing a specific album --}}
    @if(request('album'))
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-gray-800 text-lg">📁 {{ request('album') }}</h2>
            <p class="text-sm text-gray-500 mt-0.5">{{ $items->total() }} photo(s) in this album</p>
        </div>
        <a href="{{ route('admin.gallery.album-detail', request('album')) }}"
           class="btn-secondary text-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Manage &amp; Edit Photos
        </a>
    </div>
    @endif

    {{-- Photo grid --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($items->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-1 p-2">
            @foreach($items as $item)
            <div class="group relative aspect-square bg-gray-100 rounded-lg overflow-hidden">
                <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}"
                     class="w-full h-full object-cover transition group-hover:scale-105 duration-200">

                {{-- Hover overlay --}}
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/55 transition flex items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100">
                    {{-- Set cover (only if in album) --}}
                    @if($item->album)
                    <form method="POST" action="{{ route('admin.gallery.set-cover', $item) }}">
                        @csrf
                        <button type="submit" title="Set as album cover"
                                class="w-7 h-7 bg-yellow-400 rounded-full flex items-center justify-center text-yellow-900 shadow hover:bg-yellow-300 transition">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('admin.gallery.edit', $item) }}" title="Edit"
                       class="w-7 h-7 bg-white rounded-full flex items-center justify-center text-gray-700 hover:text-blue-600 shadow transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}"
                          onsubmit="return confirm('Delete this photo?')">
                        @csrf @method('DELETE')
                        <button type="submit" title="Delete"
                                class="w-7 h-7 bg-white rounded-full flex items-center justify-center text-gray-700 hover:text-red-600 shadow transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>

                {{-- Badges --}}
                <div class="absolute top-1 left-1 flex flex-col gap-0.5">
                    @if($item->album_cover)
                    <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-1.5 py-0.5 rounded leading-none">★ Cover</span>
                    @elseif($item->is_featured)
                    <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-1.5 py-0.5 rounded leading-none">★</span>
                    @endif
                    @if($item->album && !request('album'))
                    <span class="bg-blue-600 text-white text-xs px-1.5 py-0.5 rounded leading-none truncate max-w-20">{{ $item->album }}</span>
                    @endif
                </div>

                {{-- Title overlay --}}
                @if($item->title)
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent px-2 py-1.5 pointer-events-none">
                    <p class="text-white text-xs font-medium truncate">{{ $item->title }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        <div class="px-4 py-3 border-t">{{ $items->links() }}</div>
        @else
        <div class="px-4 py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="font-medium">No photos yet.</p>
            <a href="{{ route('admin.gallery.create') }}" class="inline-block mt-3 text-blue-600 hover:underline text-sm">Upload the first photo →</a>
        </div>
        @endif
    </div>
</div>
@endsection
