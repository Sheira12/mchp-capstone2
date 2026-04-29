@extends('layouts.public')

@section('title', $announcement->title)
@section('meta-description', Str::limit(strip_tags($announcement->content), 150))

@section('content')

{{-- Breadcrumb --}}
<div class="bg-gray-50 border-b">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-blue-700">Home</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('announcements') }}" class="hover:text-blue-700">Announcements</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-700">{{ Str::limit($announcement->title, 40) }}</span>
        </nav>
    </div>
</div>

{{-- Article --}}
<article class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <header class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 uppercase tracking-wide">
                    {{ $announcement->category }}
                </span>
                <span class="text-sm text-gray-500">
                    {{ $announcement->published_at?->format('F d, Y') }}
                </span>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $announcement->title }}</h1>
            @if($announcement->created_by)
            <p class="text-sm text-gray-500">
                Posted by {{ $announcement->createdBy?->name ?? 'Parish Office' }}
            </p>
            @endif
        </header>

        {{-- Featured Image --}}
        @if($announcement->image_path)
        <div class="mb-8 rounded-xl overflow-hidden shadow-lg">
            <img src="{{ Storage::url($announcement->image_path) }}" alt="{{ $announcement->title }}" class="w-full h-auto">
        </div>
        @endif

        {{-- Content --}}
        <div class="prose prose-lg max-w-none">
            {!! nl2br(e($announcement->content)) !!}
        </div>

        {{-- Expiry Notice --}}
        @if($announcement->expires_at && $announcement->expires_at->isFuture())
        <div class="mt-8 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
            <strong>Note:</strong> This announcement expires on {{ $announcement->expires_at->format('F d, Y') }}.
        </div>
        @endif

        {{-- Back Button --}}
        <div class="mt-10 pt-8 border-t">
            <a href="{{ route('announcements') }}" class="inline-flex items-center text-blue-700 hover:underline font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Announcements
            </a>
        </div>
    </div>
</article>

{{-- Related Announcements --}}
@php
    $related = \App\Models\Announcement::published()
        ->where('id', '!=', $announcement->id)
        ->where('category', $announcement->category)
        ->orderByDesc('published_at')
        ->take(3)
        ->get();
@endphp

@if($related->count())
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Announcements</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($related as $rel)
            <a href="{{ route('announcements.show', $rel) }}" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition group">
                @if($rel->image_path)
                <img src="{{ Storage::url($rel->image_path) }}" alt="{{ $rel->title }}" class="w-full h-32 object-cover">
                @else
                <div class="w-full h-32 bg-gradient-to-br from-blue-100 to-indigo-100"></div>
                @endif
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 group-hover:text-blue-700 line-clamp-2 mb-1">
                        {{ $rel->title }}
                    </h3>
                    <p class="text-xs text-gray-400">{{ $rel->published_at?->format('M d, Y') }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
