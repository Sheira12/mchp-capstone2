@extends('layouts.app')
@section('title', 'Add Video')
@section('page-title', 'Add Livestream / Video')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.livestreams.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Sunday Mass — June 29, 2026"
                       class="form-input w-full @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">YouTube URL or Video ID <span class="text-red-500">*</span></label>
                <input type="text" name="youtube_url" value="{{ old('youtube_url') }}" required id="yt-url"
                       placeholder="https://www.youtube.com/watch?v=..."
                       class="form-input w-full @error('youtube_url') border-red-400 @enderror">
                <p class="text-xs text-gray-400 mt-1">Paste a full YouTube URL, short youtu.be link, or just the 11-character video ID.</p>
                @error('youtube_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <div id="yt-preview" class="mt-3 hidden">
                    <div class="aspect-video bg-gray-900 rounded-xl overflow-hidden">
                        <iframe id="yt-iframe" src="" class="w-full h-full" frameborder="0" allow="accelerometer;autoplay;encrypted-media" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type <span class="text-red-500">*</span></label>
                    <select name="type" required class="form-select w-full">
                        <option value="recorded" {{ old('type','recorded')=='recorded'?'selected':'' }}>Recorded</option>
                        <option value="live"     {{ old('type')=='live'?'selected':'' }}>Live Now</option>
                        <option value="upcoming" {{ old('type')=='upcoming'?'selected':'' }}>Upcoming</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Scheduled Date/Time</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                           class="form-input w-full">
                </div>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-input w-full" placeholder="Optional description...">{{ old('description') }}</textarea>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-blue-600"> Active (show publicly)</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600"> Featured on homepage</label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="action-btn btn-primary">Add Video</button>
                <a href="{{ route('admin.livestreams.index') }}" class="action-btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>
function extractYtId(url) {
    const m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    return m ? m[1] : (url.trim().length === 11 ? url.trim() : null);
}
document.getElementById('yt-url').addEventListener('input', function() {
    const id = extractYtId(this.value);
    const preview = document.getElementById('yt-preview');
    const iframe = document.getElementById('yt-iframe');
    if (id) {
        iframe.src = `https://www.youtube.com/embed/${id}?rel=0`;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
        iframe.src = '';
    }
});
</script>
@endsection
