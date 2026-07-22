@extends('layouts.app')

@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <p class="text-sm text-gray-500">{{ $bookings->total() }} bookings found</p>
            @php $pendingCount = \App\Models\Booking::pending()->count(); @endphp
            @if($pendingCount > 0)
            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}"
               class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-800 text-xs font-bold px-3 py-1.5 rounded-full hover:bg-amber-200 transition animate-pulse">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                {{ $pendingCount }} Pending Approval
            </a>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.bookings.qr-scanner') }}" class="btn-secondary text-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span class="hidden sm:inline">QR Scanner</span>
                <span class="sm:hidden">QR</span>
            </a>
            <a href="{{ route('admin.bookings.calendar') }}" class="btn-secondary text-sm">
                <span class="hidden sm:inline">📅 Calendar View</span>
                <span class="sm:hidden">📅</span>
            </a>
            <a href="{{ route('admin.bookings.create') }}" class="btn-primary text-sm">+ New</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" data-live-search data-target="#bookings-table" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search parishioner or reference..." class="form-input text-sm w-full sm:flex-1 sm:min-w-48" data-live-input>
            <select name="status" class="form-select text-sm w-full sm:w-auto" data-live-input>
                <option value="">All Statuses</option>
                @foreach(\App\Models\Booking::STATUSES as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select text-sm w-full sm:w-auto" data-live-input>
                <option value="">All Types</option>
                @foreach(\App\Models\Booking::TYPES as $val => $label)
                <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex gap-2 w-full sm:w-auto">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm flex-1" data-live-input>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm flex-1" data-live-input>
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="btn-primary text-sm flex-1 sm:flex-none">Filter</button>
                @if(request()->hasAny(['search', 'status', 'type', 'date_from', 'date_to']))
                <a href="{{ route('admin.bookings.index') }}" class="btn-secondary text-sm flex-1 sm:flex-none text-center">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div id="bookings-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                        $isPending = $booking->status === 'pending';
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isPending ? 'bg-amber-50 border-l-4 border-l-amber-400' : '' }}"
                        style="{{ $isPending ? 'border-left: 4px solid #f59e0b;' : '' }}">
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
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="action-btn action-btn-view">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
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
