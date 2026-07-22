@extends('layouts.app')
@section('title', 'Edit Video')
@section('page-title', 'Edit Video')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.livestreams.update', $livestream) }}" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $livestream->title) }}" required class="form-input w-full">
            </div>
            <div>
                <label class="form-label">YouTube URL <span class="text-red-500">*</span></label>
                <input type="text" name="youtube_url" value="{{ old('youtube_url', $livestream->youtube_url) }}" required id="yt-url" class="form-input w-full">
                <div class="mt-3 aspect-video bg-gray-900 rounded-xl overflow-hidden">
                    <iframe id="yt-iframe" src="{{ $livestream->embed_url }}" class="w-full h-full" frameborder="0" allow="accelerometer;autoplay;encrypted-media" allowfullscreen></iframe>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type</label>
                    <select name="type" required class="form-select w-full">
                        @foreach(\App\Models\Livestream::TYPES as $val => $label)
                            <option value="{{ $val }}" {{ old('type', $livestream->type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Scheduled</label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $livestream->scheduled_at?->format('Y-m-d\TH:i')) }}" class="form-input w-full">
                </div>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-input w-full">{{ old('description', $livestream->description) }}</textarea>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $livestream->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600"> Active</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $livestream->is_featured) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600"> Featured</label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="action-btn btn-primary">Save Changes</button>
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
    if (id) document.getElementById('yt-iframe').src = `https://www.youtube.com/embed/${id}?rel=0`;
});
</script>
@endsection
