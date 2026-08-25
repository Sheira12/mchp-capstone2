@extends('layouts.app')
@section('title', 'Mass Schedules')
@section('page-title', 'Mass Schedules')

@section('content')
@php $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
<div class="py-6 space-y-5">

    <div class="flex justify-end">
        <a href="{{ route('admin.mass-schedules.create') }}" class="btn-primary text-sm">+ Add Schedule</a>
    </div>

    {{-- ══════════════════════════════
         REGULAR WEEKLY SCHEDULES
         ══════════════════════════════ --}}
    <div>
        <h2 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
            <span class="w-1.5 h-5 rounded-full bg-blue-600 inline-block"></span>
            Regular Weekly Schedules
        </h2>

        {{-- Mobile cards --}}
        <div class="space-y-3 lg:hidden">
            @forelse($schedules->whereNotNull('day_of_week') as $schedule)
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm {{ !$schedule->is_active ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">{{ $days[$schedule->day_of_week] }}</p>
                        <p class="text-sm text-blue-600 font-semibold mt-0.5">{{ \Carbon\Carbon::parse($schedule->time)->format('g:i A') }}</p>
                        @if($schedule->celebrant)
                        <p class="text-xs text-gray-500 mt-1">{{ $schedule->celebrant }}</p>
                        @endif
                        @if($schedule->language)
                        <p class="text-xs text-gray-400">🌐 {{ $schedule->language }}</p>
                        @endif
                        @if($schedule->notes)
                        <p class="text-xs text-gray-400 italic mt-1">{{ $schedule->notes }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0
                        {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="flex items-center gap-1.5 pt-2 border-t border-gray-100">
                    <a href="{{ route('admin.mass-schedules.edit', $schedule) }}" class="action-btn action-btn-edit">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('admin.mass-schedules.destroy', $schedule) }}" class="inline"
                          onsubmit="return confirm('Delete this schedule?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn action-btn-delete">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-gray-100 p-8 text-center text-gray-400">No regular schedules.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
            <div class="overflow-x-auto">
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
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('admin.mass-schedules.edit', $schedule) }}" class="action-btn action-btn-edit">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.mass-schedules.destroy', $schedule) }}" class="inline"
                                      onsubmit="return confirm('Delete this schedule?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No regular schedules.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════
         SPECIAL MASSES
         ══════════════════════════════ --}}
    @php $specials = $schedules->whereNotNull('special_date'); @endphp
    @if($specials->count())
    <div>
        <h2 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
            <span class="w-1.5 h-5 rounded-full bg-amber-500 inline-block"></span>
            Special Masses
        </h2>

        {{-- Mobile cards --}}
        <div class="space-y-3 lg:hidden">
            @foreach($specials as $schedule)
            <div class="bg-white border border-amber-100 rounded-xl p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">
                            {{ \Carbon\Carbon::parse($schedule->special_date)->format('M d, Y') }}
                        </p>
                        @if($schedule->special_title)
                        <p class="text-sm text-amber-700 font-semibold mt-0.5">{{ $schedule->special_title }}</p>
                        @endif
                        <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($schedule->time)->format('g:i A') }} · {{ $schedule->language }}</p>
                        @if($schedule->celebrant)
                        <p class="text-xs text-gray-400">{{ $schedule->celebrant }}</p>
                        @endif
                    </div>
                    <span class="text-xl flex-shrink-0">⭐</span>
                </div>
                <div class="flex items-center gap-1.5 pt-2 border-t border-gray-100">
                    <a href="{{ route('admin.mass-schedules.edit', $schedule) }}" class="action-btn action-btn-edit">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('admin.mass-schedules.destroy', $schedule) }}" class="inline"
                          onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn action-btn-delete">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Desktop table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
            <div class="overflow-x-auto">
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
                        <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($schedule->special_date)->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $schedule->special_title ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($schedule->time)->format('g:i A') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $schedule->language }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $schedule->celebrant ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('admin.mass-schedules.edit', $schedule) }}" class="action-btn action-btn-edit">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.mass-schedules.destroy', $schedule) }}" class="inline"
                                      onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-delete">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
