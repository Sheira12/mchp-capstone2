@extends('layouts.app')
@section('title', 'New Event')
@section('page-title', 'Create Event')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" class="space-y-5" id="ann-form">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-400 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date &amp; Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="event_start" value="{{ old('event_start') }}" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('event_start')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date &amp; Time</label>
                    <input type="datetime-local" name="event_end" value="{{ old('event_end') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Main Church, Parish Hall"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                    <select name="category" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>

                {{-- TipTap toolbar --}}
                <div id="tiptap-toolbar"
                     class="flex flex-wrap items-center gap-0.5 bg-gray-50 border border-b-0 border-gray-300 rounded-t-lg px-2 py-1.5">
                    <button type="button" class="tip-btn" data-cmd="toggleBold" title="Bold"><b>B</b></button>
                    <button type="button" class="tip-btn" data-cmd="toggleItalic" title="Italic"><i>I</i></button>
                    <button type="button" class="tip-btn" data-cmd="toggleUnderline" title="Underline"><u>U</u></button>
                    <span class="w-px h-4 bg-gray-300 mx-1"></span>
                    <button type="button" class="tip-btn" data-cmd="toggleHeading1">H1</button>
                    <button type="button" class="tip-btn" data-cmd="toggleHeading2">H2</button>
                    <span class="w-px h-4 bg-gray-300 mx-1"></span>
                    <button type="button" class="tip-btn" data-cmd="toggleBulletList">• List</button>
                    <button type="button" class="tip-btn" data-cmd="toggleOrderedList">1. List</button>
                    <span class="w-px h-4 bg-gray-300 mx-1"></span>
                    <button type="button" class="tip-btn" data-cmd="toggleBlockquote">❝</button>
                    <button type="button" class="tip-btn" data-cmd="clearNodes">✕ Fmt</button>
                </div>
                <div id="tiptap-editor"
                     class="min-h-[160px] bg-white border border-gray-300 rounded-b-lg px-4 py-3 text-sm prose max-w-none focus-within:ring-2 focus-within:ring-blue-200 focus-within:border-blue-400"
                     style="outline:none;"></div>
                <textarea name="description" id="tiptap-hidden" class="hidden">{{ old('description') }}</textarea>
            </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Event Image</label>
                <input type="file" name="image" accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:border file:border-gray-200 file:rounded-lg file:text-sm file:bg-gray-50 hover:file:bg-gray-100">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_featured" class="text-sm text-gray-700">Feature this event on the homepage</label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="action-btn btn-primary">Create Event</button>
                <a href="{{ route('admin.events.index') }}" class="action-btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@include('admin.announcements._tiptap_scripts')
@endsection
