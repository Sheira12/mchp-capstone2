@extends('layouts.app')
@section('title', 'Livestreams & Videos')
@section('page-title', 'Livestreams & Videos')

@section('content')
<div class="py-6 space-y-4">

    <div class="flex justify-end">
        <a href="{{ route('admin.livestreams.create') }}" class="btn-primary text-sm">+ Add Video / Livestream</a>
    </div>

    {{-- ── MOBILE CARDS ── --}}
    <div class="space-y-3 lg:hidden">
        @forelse($livestreams as $ls)
        @php $color = match($ls->type) { 'live'=>'red', 'upcoming'=>'amber', default=>'blue' }; @endphp
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm">
            {{-- Thumbnail banner --}}
            <div class="relative">
                <img src="{{ $ls->thumbnail }}" alt="" class="w-full h-32 object-cover bg-gray-100"
                     onerror="this.src='https://placehold.co/640x180/e2e8f0/94a3b8?text=Video'">
                <div class="absolute top-2 left-2 flex gap-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-100 text-{{ $color }}-800 shadow-sm">
                        @if($ls->type === 'live')<span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>@endif
                        {{ $ls->getTypeLabel() }}
                    </span>
                    @if($ls->is_featured)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-yellow-100 text-yellow-800 shadow-sm">★ Featured</span>
                    @endif
                </div>
                <div class="absolute top-2 right-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $ls->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }} shadow-sm">
                        {{ $ls->is_active ? 'Active' : 'Hidden' }}
                    </span>
                </div>
            </div>
            <div class="p-3">
                <p class="font-semibold text-gray-900 text-sm leading-tight">{{ $ls->title }}</p>
                @if($ls->scheduled_at)
                <p class="text-xs text-gray-400 mt-1">📅 {{ $ls->scheduled_at->format('M d, Y h:i A') }}</p>
                @endif
                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                    <a href="{{ $ls->youtube_url }}" target="_blank" class="action-btn action-btn-view">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Watch
                    </a>
                    <a href="{{ route('admin.livestreams.edit', $ls) }}" class="action-btn action-btn-edit">Edit</a>
                    <form method="POST" action="{{ route('admin.livestreams.destroy', $ls) }}" onsubmit="return confirm('Delete this video?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn action-btn-delete">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-400">No videos added yet.</div>
        @endforelse
        @if($livestreams->hasPages())
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">{{ $livestreams->links() }}</div>
        @endif
    </div>

    {{-- ── DESKTOP TABLE ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Video</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">Scheduled</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($livestreams as $ls)
                @php $color = match($ls->type) { 'live'=>'red', 'upcoming'=>'amber', default=>'blue' }; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $ls->thumbnail }}" alt="" class="w-16 h-10 object-cover rounded bg-gray-100"
                                 onerror="this.src='https://placehold.co/128x80/e2e8f0/94a3b8?text=Video'">
                            <div>
                                <p class="font-medium text-gray-900">{{ $ls->title }}</p>
                                @if($ls->is_featured)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">★ Featured</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            @if($ls->type === 'live')<span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1 animate-pulse"></span>@endif
                            {{ $ls->getTypeLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $ls->scheduled_at?->format('M d, Y h:i A') ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $ls->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                            {{ $ls->is_active ? 'Active' : 'Hidden' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-1">
                            <a href="{{ $ls->youtube_url }}" target="_blank" class="action-btn action-btn-view">Watch</a>
                            <a href="{{ route('admin.livestreams.edit', $ls) }}" class="action-btn action-btn-edit">Edit</a>
                            <form method="POST" action="{{ route('admin.livestreams.destroy', $ls) }}" onsubmit="return confirm('Delete this video?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">No videos added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($livestreams->hasPages())
        <div class="px-4 py-3 border-t">{{ $livestreams->links() }}</div>
        @endif
    </div>
</div>
@endsection
