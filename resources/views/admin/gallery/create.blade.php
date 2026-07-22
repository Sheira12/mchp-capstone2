@extends('layouts.app')
@section('title', 'Upload Photos')
@section('page-title', 'Upload Photos')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Album assignment --}}
            <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                <h3 class="text-sm font-semibold text-blue-800 mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Album (optional)
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label text-xs">Add to existing album</label>
                        <select name="album" class="form-select w-full text-sm">
                            <option value="">— No album / All Photos —</option>
                            @foreach($albums as $alb)
                            <option value="{{ $alb }}" {{ old('album') === $alb ? 'selected' : '' }}>{{ $alb }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label text-xs">Or create new album</label>
                        <input type="text" name="album_new" value="{{ old('album_new') }}"
                               class="form-input w-full text-sm" placeholder="e.g. Easter 2026">
                        <p class="text-xs text-gray-400 mt-1">If filled, this overrides the dropdown above.</p>
                    </div>
                </div>
            </div>

            {{-- Title & Caption --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Title <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-input w-full" placeholder="Photo title">
                </div>
                <div>
                    <label class="form-label">Category <span class="text-red-500">*</span></label>
                    <select name="category" class="form-select w-full" required>
                        @foreach(\App\Models\GalleryItem::CATEGORIES as $key => $label)
                        <option value="{{ $key }}" {{ old('category', 'general') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Caption <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="caption" rows="2" class="form-input w-full" placeholder="Short description…">{{ old('caption') }}</textarea>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="form-input w-full" min="0">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_featured" value="0">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-blue-600">
                        <span class="text-sm font-medium text-gray-700">Mark as Featured</span>
                    </label>
                </div>
            </div>

            {{-- File upload dropzone --}}
            <div>
                <label class="form-label">Photos <span class="text-red-500">*</span></label>
                <div id="drop-zone"
                     class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition"
                     onclick="document.getElementById('photo-input').click()">
                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm text-gray-500 font-medium">Click or drag photos here</p>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP · Max 100MB per file · Up to 200 files</p>
                </div>
                <input type="file" id="photo-input" name="images[]" multiple accept="image/*" class="hidden" onchange="previewImages(this)">
                <div id="preview-grid" class="grid grid-cols-4 sm:grid-cols-6 gap-2 mt-3 hidden"></div>
                @error('images') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                @error('images.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Upload Photos</button>
                <a href="{{ route('admin.gallery.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImages(input) {
    const grid = document.getElementById('preview-grid');
    grid.innerHTML = '';
    if (!input.files.length) { grid.classList.add('hidden'); return; }
    grid.classList.remove('hidden');
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'aspect-square rounded-lg overflow-hidden bg-gray-100';
            div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// Drag and drop
const dz = document.getElementById('drop-zone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('border-blue-400','bg-blue-50'); });
dz.addEventListener('dragleave', () => dz.classList.remove('border-blue-400','bg-blue-50'));
dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.classList.remove('border-blue-400','bg-blue-50');
    const input = document.getElementById('photo-input');
    input.files = e.dataTransfer.files;
    previewImages(input);
});
</script>
@endsection
