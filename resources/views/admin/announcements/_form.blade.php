{{-- Shared form partial for announcement create / edit --}}
@php $ann = $announcement ?? null; @endphp

<div>
    <label class="form-label">Title <span class="text-red-500">*</span></label>
    <input type="text" name="title" value="{{ old('title', $ann?->title) }}" required
           class="form-input w-full @error('title') border-red-400 @enderror"
           placeholder="Announcement title">
    @error('title')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label">Category <span class="text-red-500">*</span></label>
    <select name="category" required class="form-select w-full @error('category') border-red-400 @enderror">
        <option value="">Select category…</option>
        @foreach(\App\Models\Announcement::CATEGORIES as $val => $label)
        <option value="{{ $val }}" @selected(old('category', $ann?->category) === $val)>{{ $label }}</option>
        @endforeach
    </select>
    @error('category')<p class="form-error">{{ $message }}</p>@enderror
</div>

{{-- Rich text content area --}}
<div>
    <label class="form-label">Content <span class="text-red-500">*</span></label>

    {{-- TipTap toolbar --}}
    <div id="tiptap-toolbar"
         class="flex flex-wrap items-center gap-0.5 bg-gray-50 border border-b-0 border-gray-300 rounded-t-lg px-2 py-1.5">
        <button type="button" class="tip-btn" data-cmd="toggleBold" title="Bold"><b>B</b></button>
        <button type="button" class="tip-btn" data-cmd="toggleItalic" title="Italic"><i>I</i></button>
        <button type="button" class="tip-btn" data-cmd="toggleUnderline" title="Underline"><u>U</u></button>
        <span class="w-px h-4 bg-gray-300 mx-1"></span>
        <button type="button" class="tip-btn" data-cmd="toggleHeading1" title="Heading 1">H1</button>
        <button type="button" class="tip-btn" data-cmd="toggleHeading2" title="Heading 2">H2</button>
        <span class="w-px h-4 bg-gray-300 mx-1"></span>
        <button type="button" class="tip-btn" data-cmd="toggleBulletList" title="Bullet list">• List</button>
        <button type="button" class="tip-btn" data-cmd="toggleOrderedList" title="Numbered list">1. List</button>
        <span class="w-px h-4 bg-gray-300 mx-1"></span>
        <button type="button" class="tip-btn" data-cmd="toggleBlockquote" title="Quote">❝</button>
        <button type="button" class="tip-btn" data-cmd="clearNodes" title="Clear formatting">✕ Fmt</button>
    </div>

    {{-- TipTap editor surface --}}
    <div id="tiptap-editor"
         class="min-h-[160px] bg-white border border-gray-300 rounded-b-lg px-4 py-3 text-sm focus-within:ring-2 focus-within:ring-blue-200 focus-within:border-blue-400 prose max-w-none"
         style="outline:none;"></div>

    {{-- Hidden textarea receives HTML --}}
    <textarea name="content" id="tiptap-hidden" class="hidden" required>{{ old('content', $ann?->content) }}</textarea>
    @error('content')<p class="form-error mt-1">{{ $message }}</p>@enderror
</div>

{{-- Image upload --}}
<div>
    <label class="form-label">Thumbnail Image</label>
    @if($ann?->image_path)
    <div class="mb-2 flex items-center gap-3">
        <img src="{{ \App\Helpers\MediaHelper::url($ann->image_path) }}" alt="Current thumbnail"
             class="w-24 h-16 object-cover rounded-lg border border-gray-200">
        <span class="text-xs text-gray-500">Current thumbnail. Upload a new one to replace it.</span>
    </div>
    @endif
    <input type="file" name="image" accept="image/*"
           class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
    <p class="text-xs text-gray-400 mt-1">Max 5MB. JPG, PNG, GIF, WebP.</p>
    @error('image')<p class="form-error">{{ $message }}</p>@enderror
</div>

{{-- Publish status --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="form-label">Status <span class="text-red-500">*</span></label>
        <select name="status" id="ann-status" class="form-select w-full" onchange="toggleScheduled()">
            <option value="draft"     @selected(old('status', $ann?->status ?? 'draft') === 'draft')>Draft</option>
            <option value="published" @selected(old('status', $ann?->status) === 'published')>Publish Now</option>
            <option value="scheduled" @selected(old('status', $ann?->status) === 'scheduled')>Schedule for Later</option>
        </select>
    </div>
    <div id="scheduled-at-row" class="{{ old('status', $ann?->status) === 'scheduled' ? '' : 'hidden' }}">
        <label class="form-label">Publish Date & Time</label>
        <input type="datetime-local" name="scheduled_at"
               value="{{ old('scheduled_at', $ann?->scheduled_at?->format('Y-m-d\TH:i')) }}"
               class="form-input w-full">
        @error('scheduled_at')<p class="form-error">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="form-label">Expires At (optional)</label>
        <input type="date" name="expires_at"
               value="{{ old('expires_at', $ann?->expires_at?->format('Y-m-d')) }}"
               class="form-input w-full">
    </div>
    <div class="flex items-center gap-2 pt-6">
        <input type="checkbox" name="is_pinned" id="is_pinned" value="1"
               class="rounded border-gray-300 text-blue-600"
               @checked(old('is_pinned', $ann?->is_pinned))>
        <label for="is_pinned" class="text-sm text-gray-700 font-medium">📌 Pin to top</label>
    </div>
</div>

@push('scripts')
<script>
function toggleScheduled() {
    const status = document.getElementById('ann-status').value;
    document.getElementById('scheduled-at-row').classList.toggle('hidden', status !== 'scheduled');
}
</script>
@endpush
