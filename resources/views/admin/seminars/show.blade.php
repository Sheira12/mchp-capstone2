@extends('layouts.app')
@section('title', $seminar->title)
@section('page-title', 'Seminar Detail')

@section('content')
<div class="py-6 max-w-5xl space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.seminars.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-gray-900">{{ $seminar->title }}</h1>
            <p class="text-sm text-gray-500">{{ $seminar->service?->name }} &mdash; {{ $seminar->scheduled_at->format('F d, Y g:i A') }}</p>
        </div>
        <a href="{{ route('admin.seminars.edit', $seminar) }}" class="btn-secondary text-sm">Edit</a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @php
            $registered = $seminar->registrations->where('status', 'registered')->count();
            $attended   = $seminar->registrations->where('status', 'attended')->count();
            $absent     = $seminar->registrations->where('status', 'absent')->count();
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $registered }}</p>
            <p class="text-xs text-gray-500 mt-1 font-semibold uppercase tracking-wide">Registered</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $attended }}</p>
            <p class="text-xs text-gray-500 mt-1 font-semibold uppercase tracking-wide">Attended</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $absent }}</p>
            <p class="text-xs text-gray-500 mt-1 font-semibold uppercase tracking-wide">Absent</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-700">{{ $seminar->capacity }}</p>
            <p class="text-xs text-gray-500 mt-1 font-semibold uppercase tracking-wide">Capacity</p>
        </div>
    </div>

    {{-- Quick actions --}}
    @if($seminar->status === 'scheduled')
    <div class="flex flex-wrap gap-3">
        <form method="POST" action="{{ route('admin.seminars.complete-all', $seminar) }}">
            @csrf
            <button type="submit" class="btn-primary text-sm"
                    onclick="return confirm('Mark ALL registered attendees as attended and complete this seminar?')">
                ✓ Mark All Registered as Attended & Complete
            </button>
        </form>
    </div>
    @endif

    {{-- Attendee list --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="font-bold text-gray-900">Registrations</p>
            <p class="text-xs text-gray-400">{{ $seminar->registrations->count() }} total</p>
        </div>
        @if($seminar->registrations->isEmpty())
        <div class="py-12 text-center text-gray-400 text-sm">No registrations yet.</div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Parishioner</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">QR Token</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Registered</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Attended</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Mark</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($seminar->registrations as $reg)
                    @php $sc = ['registered'=>'blue','attended'=>'green','absent'=>'red','cancelled'=>'gray'][$reg->status] ?? 'gray'; @endphp
                    <tr class="hover:bg-gray-50" id="reg-row-{{ $reg->id }}">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $reg->parishioner?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $reg->qr_token }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $reg->registered_at?->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $reg->attended_at?->format('M d, Y g:i A') ?? '—' }}</td>
                        <td class="px-4 py-3" id="reg-status-{{ $reg->id }}">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                {{ $reg->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($seminar->status === 'scheduled')
                            <div class="flex gap-1">
                                <form method="POST" action="{{ route('admin.seminars.attendance', [$seminar, $reg]) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="attended">
                                    <button type="submit" class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-2 py-1 rounded font-semibold transition">✓ Attended</button>
                                </form>
                                <form method="POST" action="{{ route('admin.seminars.attendance', [$seminar, $reg]) }}">
                                    @csrf
                                    <input type="hidden" name="status" value="absent">
                                    <button type="submit" class="text-xs bg-red-100 text-red-700 hover:bg-red-200 px-2 py-1 rounded font-semibold transition">✗ Absent</button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
