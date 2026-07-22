@extends('layouts.app')
@section('title', 'Album: ' . $album)
@section('page-title', 'Album Management')

@section('content')
<div class="py-6 space-y-5">

    {{-- ── HEADER ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.gallery.index', ['album' => $album]) }}"
               class="w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <span class="text-2xl">📁</span> {{ $album }}
                </h1>
                <p class="text-sm text-gray-500">{{ $items->count() }} photo(s) · Click a photo to edit inline</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="selectAll()" class="px-3 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200">
                Select All
            </button>
            <button onclick="deselectAll()" class="px-3 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200">
                Deselect
            </button>
            <button onclick="deleteSelected()" class="px-3 py-2 rounded-lg text-sm font-medium bg-red-50 text-red-600 border border-red-200 hover:bg-red-100">
                🗑 Delete Selected
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- ── ADD MORE PHOTOS ── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <button onclick="toggleSection('add-photos')"
                class="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 transition">
            <div class="flex items-center gap-2 font-semibold text-gray-800">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add More Photos to This Album
                <span class="text-xs font-normal text-gray-400 ml-1">Up to 200 photos · 100 MB each</span>
            </div>
            <svg id="add-photos-chevron" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div id="add-photos" class="hidden border-t border-gray-100 p-5">
            <form method="POST" action="{{ route('admin.gallery.album-add-photos', $album) }}"
                  enctype="multipart/form-data" id="add-photos-form" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="form-label">Category <span class="text-red-500">*</span></label>
                        <select name="category" class="form-select w-full" required>
                            @foreach(\App\Models\GalleryItem::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" {{ $key === 'general' ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex items-end">
                        <div class="w-full bg-blue-50 border border-blue-100 rounded-lg px-4 py-2.5 text-sm text-blue-700">
                            <strong>Limits:</strong> Up to <strong>200 photos</strong> per upload · Max <strong>100 MB</strong> per photo · Total batch up to <strong>500 MB</strong>
                        </div>
                    </div>
                </div>

                {{-- Drop zone --}}
                <div>
                    <label class="form-label">
                        Select Photos <span class="text-red-500">*</span>
                        <span id="file-count-badge" class="hidden ml-2 px-2 py-0.5 bg-blue-600 text-white text-xs font-bold rounded-full"></span>
                    </label>

                    <div id="add-drop-zone"
                         class="relative border-2 border-dashed border-blue-200 rounded-xl p-10 text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 bg-blue-50/20"
                         onclick="document.getElementById('add-photo-input').click()"
                         ondragover="dzDragOver(event)"
                         ondragleave="dzDragLeave(event)"
                         ondrop="dzDrop(event)">

                        <div id="dz-default">
                            <svg class="w-14 h-14 mx-auto text-blue-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-base font-semibold text-blue-700">Drag &amp; drop photos here</p>
                            <p class="text-sm text-gray-400 mt-1">or click to browse your files</p>
                            <p class="text-xs text-gray-300 mt-2">JPG · PNG · WEBP · GIF &nbsp;·&nbsp; Up to 200 files · 100MB each</p>
                        </div>

                        <div id="dz-selected" class="hidden">
                            <div class="text-4xl font-bold text-blue-600" id="dz-count">0</div>
                            <p class="text-sm font-medium text-gray-700 mt-1" id="dz-size-info"></p>
                            <p class="text-xs text-gray-400 mt-1">Click to change selection</p>
                        </div>
                    </div>

                    <input type="file" id="add-photo-input" name="images[]" multiple
                           accept="image/jpeg,image/png,image/webp,image/gif"
                           class="hidden" onchange="onFilesSelected(this)">
                </div>

                {{-- Preview grid (thumbnails) --}}
                <div id="add-preview-wrap" class="hidden">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-700" id="preview-label">Preview</p>
                        <button type="button" onclick="clearFiles()" class="text-xs text-red-500 hover:underline">Clear all</button>
                    </div>
                    <div id="add-preview-grid"
                         class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-1.5 max-h-64 overflow-y-auto rounded-lg p-2 bg-gray-50 border border-gray-100"></div>
                </div>

                {{-- Upload progress --}}
                <div id="upload-progress-wrap" class="hidden">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-sm font-medium text-gray-700">Uploading…</span>
                        <span id="upload-pct" class="text-sm font-bold text-blue-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div id="upload-bar" class="h-3 bg-blue-600 rounded-full transition-all duration-200" style="width:0%"></div>
                    </div>
                    <p id="upload-status" class="text-xs text-gray-400 mt-1.5"></p>
                </div>

                {{-- Warnings --}}
                <div id="upload-warnings" class="hidden bg-amber-50 border border-amber-200 rounded-lg p-3 text-sm text-amber-800"></div>

                <div class="flex gap-3 items-center">
                    <button type="button" id="upload-btn" onclick="submitWithProgress()"
                            class="btn-primary flex items-center gap-2" disabled>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span id="upload-btn-text">Upload Photos</span>
                    </button>
                    <button type="button" onclick="toggleSection('add-photos')" class="btn-secondary">Cancel</button>
                    <span id="upload-file-summary" class="text-sm text-gray-400 ml-2"></span>
                </div>
            </form>
        </div>
    </div>

    {{-- ── BULK EDIT PHOTOS ── --}}
    @if($items->count())
    <form method="POST" action="{{ route('admin.gallery.bulk-update', $album) }}"
          enctype="multipart/form-data" id="bulk-form">
        @csrf

        <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold text-gray-800 text-base">Edit Photos in This Album</h2>
            <button type="submit" class="btn-primary text-sm px-4 py-2">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save All Changes
            </button>
        </div>

        <div class="space-y-3" id="photo-list">
            @foreach($items as $idx => $item)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden photo-card"
                 data-id="{{ $item->id }}">

                {{-- Row header --}}
                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-b border-gray-100">
                    {{-- Select checkbox --}}
                    <input type="checkbox" class="photo-checkbox w-4 h-4 rounded text-red-500"
                           value="{{ $item->id }}" onchange="updateDeleteBtn()">

                    {{-- Thumbnail --}}
                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-200 flex-shrink-0 cursor-pointer"
                         onclick="toggleEdit({{ $item->id }})">
                        <img src="{{ Storage::url($item->image_path) }}" alt=""
                             class="w-full h-full object-cover">
                    </div>

                    {{-- Info summary --}}
                    <div class="flex-1 min-w-0 cursor-pointer" onclick="toggleEdit({{ $item->id }})">
                        <p class="font-medium text-gray-800 text-sm truncate">
                            {{ $item->title ?: 'Untitled' }}
                            @if($item->album_cover)
                            <span class="ml-1 text-yellow-500 text-xs">★ Cover</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $item->category_label }}
                            @if($item->caption) · {{ Str::limit($item->caption, 40) }} @endif
                        </p>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <button type="button" onclick="toggleEdit({{ $item->id }})"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}"
                              onsubmit="return confirm('Delete this photo?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </form>
                        @if(!$item->album_cover)
                        <form method="POST" action="{{ route('admin.gallery.set-cover', $item) }}" class="inline">
                            @csrf
                            <button type="submit" title="Set as album cover"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium bg-yellow-50 text-yellow-700 hover:bg-yellow-100 transition flex items-center gap-1">
                                ★ Cover
                            </button>
                        </form>
                        @else
                        <span class="px-3 py-1.5 rounded-lg text-xs font-medium bg-yellow-100 text-yellow-800">★ Cover</span>
                        @endif
                    </div>
                </div>

                {{-- Expandable edit fields --}}
                <div id="edit-{{ $item->id }}" class="hidden p-4">
                    <input type="hidden" name="photos[{{ $idx }}][id]" value="{{ $item->id }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Title --}}
                        <div class="lg:col-span-2">
                            <label class="form-label text-xs">Title</label>
                            <input type="text" name="photos[{{ $idx }}][title]"
                                   value="{{ old('photos.'.$idx.'.title', $item->title) }}"
                                   class="form-input w-full text-sm" placeholder="Photo title…">
                        </div>

                        {{-- Sort order --}}
                        <div>
                            <label class="form-label text-xs">Sort Order</label>
                            <input type="number" name="photos[{{ $idx }}][sort_order]"
                                   value="{{ old('photos.'.$idx.'.sort_order', $item->sort_order) }}"
                                   class="form-input w-full text-sm" min="0">
                        </div>

                        {{-- Checkboxes --}}
                        <div class="flex flex-col gap-2 justify-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="photos[{{ $idx }}][is_featured]"
                                       value="1" {{ $item->is_featured ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-blue-600">
                                <span class="text-xs text-gray-700">Featured</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="photos[{{ $idx }}][album_cover]"
                                       value="1" {{ $item->album_cover ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-yellow-500 album-cover-cb"
                                       onchange="onlyCover(this)">
                                <span class="text-xs text-gray-700">Set as Cover ★</span>
                            </label>
                        </div>
                    </div>

                    {{-- Caption --}}
                    <div class="mt-3">
                        <label class="form-label text-xs">Caption</label>
                        <textarea name="photos[{{ $idx }}][caption]" rows="2"
                                  class="form-input w-full text-sm"
                                  placeholder="Short description…">{{ old('photos.'.$idx.'.caption', $item->caption) }}</textarea>
                    </div>

                    {{-- Replace photo --}}
                    <div class="mt-3">
                        <label class="form-label text-xs">Replace Photo <span class="text-gray-400">(optional)</span></label>
                        <div class="flex items-center gap-3">
                            <img src="{{ Storage::url($item->image_path) }}" alt=""
                                 class="w-16 h-16 object-cover rounded-lg border border-gray-200 flex-shrink-0">
                            <div class="flex-1">
                                <input type="file" name="photos[{{ $idx }}][replace]"
                                       accept="image/*" class="form-input w-full text-sm"
                                       onchange="previewReplace(this, {{ $item->id }})">
                                <p class="text-xs text-gray-400 mt-1">Upload a new image to replace the current one.</p>
                            </div>
                            <img id="replace-preview-{{ $item->id }}" src="" alt=""
                                 class="w-16 h-16 object-cover rounded-lg border border-blue-200 hidden">
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4 flex gap-3">
            <button type="submit" class="btn-primary">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save All Changes
            </button>
            <a href="{{ route('admin.gallery.index', ['album' => $album]) }}" class="btn-secondary">
                View in Gallery
            </a>
        </div>
    </form>

    {{-- Bulk delete hidden form --}}
    <form method="POST" action="{{ route('admin.gallery.bulk-delete') }}" id="bulk-delete-form">
        @csrf
        <div id="bulk-delete-ids"></div>
    </form>

    @else
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-16 text-center text-gray-400">
        <div class="text-5xl mb-3">📭</div>
        <p class="font-medium text-gray-500">No photos in this album yet.</p>
        <p class="text-sm mt-1">Use the form above to add photos.</p>
    </div>
    @endif

</div>

<script>
// ─────────────────────────────────────────────
//  STATE
// ─────────────────────────────────────────────
let selectedFiles = [];
const MAX_FILES   = 200;
const MAX_SIZE_MB = 100;

// ─────────────────────────────────────────────
//  DROPZONE DRAG EVENTS
// ─────────────────────────────────────────────
function dzDragOver(e) {
    e.preventDefault();
    document.getElementById('add-drop-zone').classList.add('border-blue-500','bg-blue-100/60','scale-[1.01]');
}
function dzDragLeave() {
    document.getElementById('add-drop-zone').classList.remove('border-blue-500','bg-blue-100/60','scale-[1.01]');
}
function dzDrop(e) {
    e.preventDefault(); dzDragLeave();
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
    const input = document.getElementById('add-photo-input');
    input.files = dt.files;
    onFilesSelected(input);
}

// ─────────────────────────────────────────────
//  FILE SELECTION + VALIDATION
// ─────────────────────────────────────────────
function onFilesSelected(input) {
    const files = Array.from(input.files);
    const warnings = [];

    selectedFiles = files.filter(f => {
        if (!f.type.startsWith('image/')) { warnings.push(`"${f.name}" is not an image — skipped.`); return false; }
        if (f.size > MAX_SIZE_MB * 1024 * 1024) { warnings.push(`"${f.name}" exceeds ${MAX_SIZE_MB}MB — skipped.`); return false; }
        return true;
    }).slice(0, MAX_FILES);

    if (files.length > MAX_FILES) warnings.push(`Only first ${MAX_FILES} photos kept (${files.length} provided).`);
    showWarnings(warnings);
    updateDropzoneUI();
    renderPreviews();
    syncFilesToInput();
}

function updateDropzoneUI() {
    const def = document.getElementById('dz-default');
    const sel = document.getElementById('dz-selected');
    const badge = document.getElementById('file-count-badge');
    const btn = document.getElementById('upload-btn');
    const summary = document.getElementById('upload-file-summary');

    if (!selectedFiles.length) {
        def?.classList.remove('hidden'); sel?.classList.add('hidden');
        badge?.classList.add('hidden'); if(btn) btn.disabled=true; if(summary) summary.textContent=''; return;
    }
    def?.classList.add('hidden'); sel?.classList.remove('hidden');
    const totalMB = (selectedFiles.reduce((s,f)=>s+f.size,0)/1024/1024).toFixed(1);
    if(document.getElementById('dz-count')) document.getElementById('dz-count').textContent = selectedFiles.length;
    if(document.getElementById('dz-size-info')) document.getElementById('dz-size-info').textContent = `${selectedFiles.length} photo${selectedFiles.length>1?'s':''} · ${totalMB} MB total`;
    if(badge) { badge.textContent=selectedFiles.length; badge.classList.remove('hidden'); }
    if(btn) { btn.disabled=false; }
    if(document.getElementById('upload-btn-text')) document.getElementById('upload-btn-text').textContent = `Upload ${selectedFiles.length} Photo${selectedFiles.length>1?'s':''}`;
    if(summary) summary.textContent = `${selectedFiles.length} file${selectedFiles.length>1?'s':''} · ${totalMB} MB`;
}

function renderPreviews() {
    const wrap = document.getElementById('add-preview-wrap');
    const grid = document.getElementById('add-preview-grid');
    const label = document.getElementById('preview-label');
    if (!grid) return;
    grid.innerHTML = '';
    if (!selectedFiles.length) { wrap?.classList.add('hidden'); return; }
    wrap?.classList.remove('hidden');
    if(label) label.textContent = `Preview — ${selectedFiles.length} photo${selectedFiles.length>1?'s':''}`;
    selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'relative aspect-square rounded-lg overflow-hidden bg-gray-200 group cursor-pointer';
            div.title = file.name;
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-full object-cover">
                <button type="button" onclick="removeFile(${idx})"
                    class="absolute top-0.5 right-0.5 w-5 h-5 bg-red-500 text-white rounded-full text-xs
                           flex items-center justify-center opacity-0 group-hover:opacity-100 transition">✕</button>
                <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs px-1 py-0.5 truncate
                            opacity-0 group-hover:opacity-100 transition">${(file.size/1024/1024).toFixed(1)}MB</div>`;
            grid.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeFile(idx) {
    selectedFiles.splice(idx,1); syncFilesToInput(); updateDropzoneUI(); renderPreviews();
}

function clearFiles() {
    selectedFiles=[];
    const inp = document.getElementById('add-photo-input'); if(inp) inp.value='';
    updateDropzoneUI(); renderPreviews();
    document.getElementById('add-preview-wrap')?.classList.add('hidden');
    document.getElementById('dz-default')?.classList.remove('hidden');
    document.getElementById('dz-selected')?.classList.add('hidden');
}

function syncFilesToInput() {
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    const inp = document.getElementById('add-photo-input');
    if(inp) inp.files = dt.files;
}

function showWarnings(list) {
    const el = document.getElementById('upload-warnings');
    if(!el) return;
    if(!list.length) { el.classList.add('hidden'); return; }
    el.classList.remove('hidden');
    el.innerHTML = '<strong>⚠ Notices:</strong><ul class="mt-1 space-y-0.5 list-disc list-inside">' + list.map(w=>`<li>${w}</li>`).join('') + '</ul>';
}

// ─────────────────────────────────────────────
//  XHR UPLOAD WITH PROGRESS BAR
// ─────────────────────────────────────────────
function submitWithProgress() {
    if (!selectedFiles.length) return;
    const form=document.getElementById('add-photos-form');
    const btn=document.getElementById('upload-btn');
    const bar=document.getElementById('upload-bar');
    const pct=document.getElementById('upload-pct');
    const status=document.getElementById('upload-status');
    const wrap=document.getElementById('upload-progress-wrap');

    btn.disabled=true;
    btn.innerHTML='<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> <span>Uploading…</span>';
    wrap.classList.remove('hidden');
    bar.style.width='0%'; pct.textContent='0%';
    status.textContent=`Uploading ${selectedFiles.length} photo${selectedFiles.length>1?'s':''}…`;

    const data = new FormData(form);
    const xhr = new XMLHttpRequest();

    xhr.upload.addEventListener('progress', e => {
        if (e.lengthComputable) {
            const p = Math.round(e.loaded/e.total*100);
            bar.style.width = p+'%'; pct.textContent = p+'%';
            status.textContent = `${(e.loaded/1024/1024).toFixed(1)} MB / ${(e.total/1024/1024).toFixed(1)} MB`;
        }
    });

    xhr.addEventListener('load', () => {
        if (xhr.status >= 200 && xhr.status < 400) {
            bar.style.width='100%'; pct.textContent='100%';
            bar.classList.remove('bg-blue-600'); bar.classList.add('bg-green-500');
            status.textContent='Upload complete! Refreshing…';
            setTimeout(()=>window.location.reload(), 700);
        } else {
            bar.classList.remove('bg-blue-600'); bar.classList.add('bg-red-500');
            status.textContent='Upload failed (HTTP '+xhr.status+'). Try again.';
            btn.disabled=false; btn.innerHTML='<span>Retry Upload</span>';
        }
    });
    xhr.addEventListener('error', () => {
        bar.classList.add('bg-red-500');
        status.textContent='Network error. Check connection and retry.';
        btn.disabled=false; btn.innerHTML='<span>Retry Upload</span>';
    });

    xhr.open('POST', form.action);
    xhr.setRequestHeader('X-Requested-With','XMLHttpRequest');
    xhr.send(data);
}

// ─────────────────────────────────────────────
//  SECTION TOGGLE
// ─────────────────────────────────────────────
function toggleSection(id) {
    const el=document.getElementById(id);
    const ch=document.getElementById(id+'-chevron');
    el?.classList.toggle('hidden');
    ch?.classList.toggle('rotate-180');
}

// ─────────────────────────────────────────────
//  INDIVIDUAL PHOTO EDIT
// ─────────────────────────────────────────────
function toggleEdit(id) {
    const el=document.getElementById('edit-'+id);
    if (el) { el.classList.toggle('hidden'); if(!el.classList.contains('hidden')) el.scrollIntoView({behavior:'smooth',block:'nearest'}); }
}
function onlyCover(cb) {
    if (cb.checked) document.querySelectorAll('.album-cover-cb').forEach(c => { if(c!==cb) c.checked=false; });
}
function previewReplace(input, id) {
    const p=document.getElementById('replace-preview-'+id);
    if (input.files&&input.files[0]) { const r=new FileReader(); r.onload=e=>{p.src=e.target.result;p.classList.remove('hidden');}; r.readAsDataURL(input.files[0]); }
}

// ─────────────────────────────────────────────
//  BULK SELECT / DELETE
// ─────────────────────────────────────────────
function selectAll()   { document.querySelectorAll('.photo-checkbox').forEach(cb=>cb.checked=true); }
function deselectAll() { document.querySelectorAll('.photo-checkbox').forEach(cb=>cb.checked=false); }
function deleteSelected() {
    const checked=Array.from(document.querySelectorAll('.photo-checkbox:checked')).map(cb=>cb.value);
    if (!checked.length) { alert('Select at least one photo.'); return; }
    if (!confirm(`Permanently delete ${checked.length} photo(s)?`)) return;
    const form=document.getElementById('bulk-delete-form');
    const cont=document.getElementById('bulk-delete-ids');
    cont.innerHTML='';
    checked.forEach(id=>{ const inp=document.createElement('input'); inp.type='hidden'; inp.name='ids[]'; inp.value=id; cont.appendChild(inp); });
    form.submit();
}
</script>
@endsection
