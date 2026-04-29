@extends('layouts.app')

@section('title', 'New Announcement')
@section('page-title', 'New Announcement')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="form-label">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="form-input w-full @error('title') border-red-400 @enderror"
                       placeholder="Announcement title">
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Category <span class="text-red-500">*</span></label>
                <select name="category" required class="form-select w-full @error('category') border-red-400 @enderror">
                    <option value="">Select category…</option>
                    <option value="general" @selected(old('category') === 'general')>General</option>
                    <option value="mass" @selected(old('category') === 'mass')>Mass Schedule</option>
                    <option value="event" @selected(old('category') === 'event')>Event</option>
                    <option value="sacrament" @selected(old('category') === 'sacrament')>Sacrament</option>
                    <option value="notice" @selected(old('category') === 'notice')>Notice</option>
                </select>
                @error('category')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Content <span class="text-red-500">*</span></label>
                <textarea name="content" rows="6" required
                          class="form-input w-full @error('content') border-red-400 @enderror"
                          placeholder="Announcement content…">{{ old('content') }}</textarea>
                @error('content')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Image (optional)</label>
                <input type="file" name="image" accept="image/*" class="form-input w-full">
                @error('image')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Expires At (optional)</label>
                <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="form-input w-full">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_published" id="is_published" value="1"
                       class="rounded border-gray-300 text-blue-600" @checked(old('is_published'))>
                <label for="is_published" class="text-sm text-gray-700">Publish immediately</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Create Announcement</button>
                <a href="{{ route('admin.announcements.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
