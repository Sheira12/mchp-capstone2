@extends('layouts.app')
@section('title', 'New Announcement')
@section('page-title', 'New Announcement')

@section('content')
<div class="py-6 max-w-3xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.announcements.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900">New Announcement</h1>
    </div>

    @if(session('warning'))
    <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 text-sm">{{ session('warning') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data" class="space-y-5" id="ann-form">
            @csrf
            @include('admin.announcements._form')
            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="btn-primary">Create Announcement</button>
                <a href="{{ route('admin.announcements.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@include('admin.announcements._tiptap_scripts')
@endsection
