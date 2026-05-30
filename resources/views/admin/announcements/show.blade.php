@extends('layouts.app')
@section('title', $announcement->title)
@section('page-title', 'Announcement')

@section('content')
<div class="py-6 max-w-3xl space-y-5">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('admin.announcements.index') }}"
           class="text-sm text-blue-600 hover:underline flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            All Announcements
        </a>
        <div class="flex gap-2">
            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn-secondary text-sm">Edit</a>
            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST"
                  onsubmit="return confirm('Delete this announcement?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-sm text-red-600 border-red-200 hover:bg-red-50">Delete</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($announcement->image_path)
        <img src="{{ Storage::url($announcement->image_path) }}" alt="{{ $announcement->title }}"
             class="w-full h-56 object-cover">
        @endif
        <div class="p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 mb-2">
                        {{ ucfirst($announcement->category) }}
                    </span>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $announcement->title }}</h1>
                    <p class="text-sm text-gray-400 mt-1">
                        By {{ $announcement->createdBy?->name ?? 'Admin' }}
                        · {{ $announcement->created_at->format('F d, Y g:i A') }}
                    </p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold flex-shrink-0
                    {{ $announcement->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $announcement->is_published ? 'Published' : 'Draft' }}
                </span>
            </div>

            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed border-t border-gray-100 pt-4">
                {!! nl2br(e($announcement->content)) !!}
            </div>

            @if($announcement->expires_at)
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2 text-sm text-amber-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Expires: {{ $announcement->expires_at->format('F d, Y') }}
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
