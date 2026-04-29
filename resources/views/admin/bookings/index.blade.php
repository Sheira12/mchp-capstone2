@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500">{{ $bookings->total() }} bookings found</p>
        <div class="flex gap-2">
            <a href="{{ route('admin.bookings.calendar') }}" class="btn-secondary text-sm">📅 Calendar View</a>
            <a href="{{ route('admin.bookings.create') }}" class="btn-primary text-sm">+ New Booking</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search parishioner or reference..." class="form-input text-sm flex-1 min-w-48">
            <select name="status" class="form-select text-sm">
                <option value="">All Statuses</option>
                @foreach(\App\Models\Booking::STATUSES as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select text-sm">
                <option value="">All Types</option>
                @foreach(\App\Models\Booking::TYPES as $val => $label)
                <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm">
            <button type="submit" class="btn-primary text-sm">Filter</button>
            @if(request()->hasAny(['search', 'status', 'type', 'date_from', 'date_to']))
            <a href="{{ route('admin.bookings.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Reference</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Parishioner</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Service</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Scheduled</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Fee</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($bookings as $booking)
                    @php
                        $statusColors = ['pending' => 'amber', 'confirmed' => 'green', 'completed' => 'blue', 'cancelled' => 'red'];
                        $color = $statusColors[$booking->status] ?? 'gray';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $booking->reference_number }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.parishioners.show', $booking->parishioner) }}" class="font-medium text-gray-900 hover:text-blue-700">
                                {{ $booking->parishioner->full_name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $booking->getTypeLabel() }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $booking->scheduled_date->format('M d, Y') }}
                            @if($booking->scheduled_time)
                            <span class="text-gray-400">{{ \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">₱{{ number_format($booking->service_fee, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                {{ $booking->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="text-blue-600 hover:text-blue-800 text-xs">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">No bookings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
