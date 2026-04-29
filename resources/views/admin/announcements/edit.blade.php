@extends('layouts.app')

@section('title', 'Edit Announcement')
@section('page-title', 'Edit Announcement')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $announcement->title) }}" required
                       class="form-input w-full @error('title') border-red-400 @enderror">
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Category <span class="text-red-500">*</span></label>
                <select name="category" required class="form-select w-full">
                    <option value="general" @selected(old('category', $announcement->category) === 'general')>General</option>
                    <option value="mass" @selected(old('category', $announcement->category) === 'mass')>Mass Schedule</option>
                    <option value="event" @selected(old('category', $announcement->category) === 'event')>Event</option>
                    <option value="sacrament" @selected(old('category', $announcement->category) === 'sacrament')>Sacrament</option>
                    <option value="notice" @selected(old('category', $announcement->category) === 'notice')>Notice</option>
                </select>
            </div>

            <div>
                <label class="form-label">Content <span class="text-red-500">*</span></label>
                <textarea name="content" rows="6" required class="form-input w-full">{{ old('content', $announcement->content) }}</textarea>
            </div>

            @if($announcement->image_path)
            <div>
                <label class="form-label">Current Image</label>
                <img src="{{ Storage::url($announcement->image_path) }}" class="w-32 h-20 object-cover rounded mb-2">
            </div>
            @endif

            <div>
                <label class="form-label">Replace Image (optional)</label>
                <input type="file" name="image" accept="image/*" class="form-input w-full">
            </div>

            <div>
                <label class="form-label">Expires At</label>
                <input type="date" name="expires_at"
                       value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d')) }}"
                       class="form-input w-full">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_published" id="is_published" value="1"
                       class="rounded border-gray-300 text-blue-600"
                       @checked(old('is_published', (bool)$announcement->published_at))>
                <label for="is_published" class="text-sm text-gray-700">Published</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.announcements.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
