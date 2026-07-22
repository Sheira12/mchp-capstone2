@extends('layouts.app')
@section('title', 'Edit Event')
@section('page-title', 'Edit Event')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $event->title) }}" required
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date &amp; Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="event_start" value="{{ old('event_start', $event->event_start->format('Y-m-d\TH:i')) }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date &amp; Time</label>
                    <input type="datetime-local" name="event_end" value="{{ old('event_end', $event->event_end?->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" value="{{ old('location', $event->location) }}"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $event->category) == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="published" {{ old('status', $event->status) == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ old('status', $event->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="cancelled" {{ old('status', $event->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="5"
                          class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $event->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Image</label>
                @if($event->image_path)
                    <img src="{{ Storage::url($event->image_path) }}" class="w-32 h-20 object-cover rounded mb-2">
                @endif
                <input type="file" name="image" accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:border file:border-gray-200 file:rounded-lg file:text-sm file:bg-gray-50 hover:file:bg-gray-100">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured', $event->is_featured) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_featured" class="text-sm text-gray-700">Feature this event on the homepage</label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="action-btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.events.show', $event) }}" class="action-btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
