@extends('layouts.portal')

@section('title', 'My Bookings')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Bookings</h1>
            <p class="text-sm text-gray-500 mt-1">Track and manage your parish service appointments</p>
        </div>
        <a href="{{ route('parishioner.bookings.create') }}"
           class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all text-sm self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Booking
        </a>
    </div>

    @if($bookings->count())

    {{-- Status filter tabs --}}
    <div class="flex gap-2 flex-wrap">
        @foreach(['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'] as $val => $label)
        <a href="{{ request()->fullUrlWithQuery(['status' => $val === 'all' ? null : $val]) }}"
           class="px-4 py-1.5 rounded-full text-sm font-semibold transition
           {{ (request('status', 'all') === $val) ? 'bg-blue-600 text-white shadow' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300 hover:text-blue-600' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse($bookings as $booking)
        @php
            $statusColors = ['pending'=>'amber','confirmed'=>'green','completed'=>'blue','cancelled'=>'red'];
            $sc = $statusColors[$booking->status] ?? 'gray';
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition overflow-hidden">
            {{-- Status bar --}}
            <div class="h-1 bg-{{ $sc }}-400"></div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        {{-- Icon --}}
                        <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 text-base">{{ $booking->getTypeLabel() }}</h3>
                            <div class="flex flex-wrap items-center gap-3 mt-1.5">
                                <span class="flex items-center gap-1 text-sm text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $booking->scheduled_date->format('M d, Y') }}
                                </span>
                                @if($booking->scheduled_time)
                                <span class="flex items-center gap-1 text-sm text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') }}
                                </span>
                                @endif
                                @if($booking->service_fee > 0)
                                <span class="flex items-center gap-1 text-sm text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    ₱{{ number_format($booking->service_fee, 2) }}
                                </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 font-mono mt-1.5">{{ $booking->reference_number }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-3 flex-shrink-0">
                        <span class="badge badge-{{ $booking->status }}">{{ $booking->getStatusLabel() }}</span>
                        <a href="{{ route('parishioner.bookings.show', $booking) }}"
                           class="text-xs font-semibold text-blue-600 hover:underline flex items-center gap-1">
                            View Details
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        {{-- Filtered-empty state: has bookings but current filter returns nothing --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-600 mb-1">No bookings match this filter</p>
            <p class="text-sm text-gray-400 mb-4">Try selecting a different status or
                <a href="{{ route('parishioner.bookings.index') }}" class="text-blue-600 hover:underline font-medium">view all bookings</a>.
            </p>
        </div>
        @endforelse
    </div>

    <div>{{ $bookings->links() }}</div>

    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">No bookings yet</h3>
        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">Book a parish service to get started. We offer baptism, marriage, blessings, and more.</p>
        <a href="{{ route('parishioner.bookings.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-blue-700 shadow-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Book a Service
        </a>
    </div>
    @endif
</div>
@endsection
