@extends('layouts.app')
@section('title', 'Edit Photo')
@section('page-title', 'Edit Photo')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        {{-- Current photo --}}
        <div class="mb-6 flex gap-4 items-start">
            <img src="{{ Storage::url($gallery->image_path) }}" alt="{{ $gallery->title }}"
                 class="w-32 h-32 object-cover rounded-xl border border-gray-200">
            <div>
                <p class="font-medium text-gray-800">{{ $gallery->title ?: 'Untitled Photo' }}</p>
                @if($gallery->album)
                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $gallery->album }}
                </span>
                @endif
                <p class="text-xs text-gray-400 mt-1">Uploaded {{ $gallery->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.gallery.update', $gallery) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            {{-- Album assignment --}}
            <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                <h3 class="text-sm font-semibold text-blue-800 mb-3">Album</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs">Existing album</label>
                        <select name="album" class="form-select w-full text-sm">
                            <option value="">— No album —</option>
                            @foreach($albums as $alb)
                            <option value="{{ $alb }}" {{ old('album', $gallery->album) === $alb ? 'selected' : '' }}>{{ $alb }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Or create new album</label>
                        <input type="text" name="album_new" value="{{ old('album_new') }}"
                               class="form-input w-full text-sm" placeholder="New album name…">
                    </div>
                </div>
                @if($gallery->album)
                <div class="mt-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="album_cover" value="0">
                        <input type="checkbox" name="album_cover" value="1"
                               {{ old('album_cover', $gallery->album_cover) ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-yellow-500">
                        <span class="text-sm text-gray-700 font-medium">Set as album cover photo ★</span>
                    </label>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Title</label>
                    <input type="text" name="title" value="{{ old('title', $gallery->title) }}" class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Category <span class="text-red-500">*</span></label>
                    <select name="category" class="form-select w-full" required>
                        @foreach(\App\Models\GalleryItem::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" {{ old('category', $gallery->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Caption</label>
                <textarea name="caption" rows="2" class="form-input w-full">{{ old('caption', $gallery->caption) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $gallery->sort_order) }}" class="form-input w-full" min="0">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $gallery->is_featured) ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-blue-600">
                        <span class="text-sm font-medium text-gray-700">Featured</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="form-label">Replace Photo <span class="text-gray-400 font-normal">(optional)</span></label>
                <input type="file" name="image" accept="image/*" class="form-input w-full text-sm">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.gallery.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
