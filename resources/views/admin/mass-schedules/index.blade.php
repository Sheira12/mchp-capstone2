@extends('layouts.app')

@section('title', 'Mass Schedules')
@section('page-title', 'Mass Schedules')

@section('content')
@php
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
@endphp
<div class="py-6 space-y-4">

    <div class="flex justify-end">
        <a href="{{ route('admin.mass-schedules.create') }}" class="btn-primary text-sm">+ Add Schedule</a>
    </div>

    {{-- Regular Schedules --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Regular Weekly Schedules</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Day</th>
                    <th class="px-4 py-3 font-medium">Time</th>
                    <th class="px-4 py-3 font-medium">Language</th>
                    <th class="px-4 py-3 font-medium">Celebrant</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Notes</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($schedules->whereNotNull('day_of_week') as $schedule)
                <tr class="hover:bg-gray-50 {{ !$schedule->is_active ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $days[$schedule->day_of_week] }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ \Carbon\Carbon::parse($schedule->time)->format('g:i A') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $schedule->language }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $schedule->celebrant ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($schedule->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $schedule->notes ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.mass-schedules.edit', $schedule) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.mass-schedules.destroy', $schedule) }}"
                                  onsubmit="return confirm('Delete this schedule?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-400">No regular schedules.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Special Masses --}}
    @php $specials = $schedules->whereNotNull('special_date'); @endphp
    @if($specials->count())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Special Masses</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Title</th>
                    <th class="px-4 py-3 font-medium">Time</th>
                    <th class="px-4 py-3 font-medium">Language</th>
                    <th class="px-4 py-3 font-medium">Celebrant</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($specials as $schedule)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ \Carbon\Carbon::parse($schedule->special_date)->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $schedule->special_title ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($schedule->time)->format('g:i A') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $schedule->language }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $schedule->celebrant ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.mass-schedules.edit', $schedule) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.mass-schedules.destroy', $schedule) }}"
                                  onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
