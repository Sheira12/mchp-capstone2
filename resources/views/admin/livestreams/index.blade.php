@extends('layouts.app')
@section('title', 'Livestreams & Videos')
@section('page-title', 'Livestreams & Videos')

@section('content')
<div class="py-6 space-y-4">
    <div class="flex justify-end">
        <a href="{{ route('admin.livestreams.create') }}" class="action-btn btn-primary">+ Add Video / Livestream</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $ls->thumbnail }}" alt="" class="w-16 h-10 object-cover rounded bg-gray-100"
                                 onerror="this.src='https://via.placeholder.com/128x80?text=Video'">
                            <div>
                                <p class="font-medium text-gray-900">{{ $ls->title }}</p>
                                @if($ls->is_featured)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">★ Featured</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $color = match($ls->type) { 'live'=>'red', 'upcoming'=>'amber', default=>'blue' };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            @if($ls->type === 'live') <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1 animate-pulse"></span> @endif
                            {{ $ls->getTypeLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $ls->scheduled_at?->format('M d, Y h:i A') ?? '—' }}</td>
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
        @if($livestreams->hasPages())
        <div class="px-4 py-3 border-t">{{ $livestreams->links() }}</div>
        @endif
    </div>
</div>
@endsection
