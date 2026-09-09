@extends('layouts.app')
@section('title', 'Bookings')
@section('page-title', 'Bookings')

@push('styles')
<style>
.bk-card { background:#fff; border:1px solid #e8edf5; border-radius:1rem; padding:1rem; transition:box-shadow 0.15s; overflow:hidden; }
.bk-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.07); }
.bk-card.pending { border-left:4px solid #f59e0b; background:#fffbeb; }
</style>
@endpush

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            <p class="text-sm text-gray-500"><span class="font-semibold text-gray-800">{{ $bookings->total() }}</span> bookings found</p>
            @php $pendingCount = $pendingCount ?? 0; @endphp
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
                <span class="hidden sm:inline">QR Scanner</span><span class="sm:hidden">QR</span>
            </a>
            <a href="{{ route('admin.bookings.calendar') }}" class="btn-secondary text-sm">
                <span class="hidden sm:inline">📅 Calendar</span><span class="sm:hidden">📅</span>
            </a>
            <a href="{{ route('admin.bookings.create') }}" class="btn-primary text-sm">+ New</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div class="flex-1 min-w-48">
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search parishioner or reference..."
                           class="form-input text-sm w-full pl-9">
                </div>
            </div>
            <select name="status" class="form-select text-sm" style="min-width:130px;">
                <option value="">All Statuses</option>
                @foreach(\App\Models\Booking::STATUSES as $val => $label)
                <option value="{{ $val }}" {{ request('status')===$val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="type" class="form-select text-sm" style="min-width:150px;">
                <option value="">All Services</option>
                @foreach(\App\Models\Booking::TYPES as $val => $label)
                <option value="{{ $val }}" {{ request('type')===$val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="form-input text-sm" style="min-width:140px;">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="form-input text-sm" style="min-width:140px;">
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter
            </button>
            @if(request()->hasAny(['search','status','type','date_from','date_to']))
            <a href="{{ route('admin.bookings.index') }}"
               class="px-4 py-2 border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium rounded-lg transition whitespace-nowrap">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- ── MOBILE CARDS ── --}}
    <div id="bookings-list" class="space-y-3 lg:hidden">
        @forelse($bookings as $booking)
        @php
            $statusColors = ['pending'=>'amber','confirmed'=>'green','completed'=>'blue','cancelled'=>'red'];
            $color = $statusColors[$booking->status] ?? 'gray';
            $isPending = $booking->status === 'pending';
        @endphp
        <div class="bk-card {{ $isPending ? 'pending' : '' }}">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-900 text-sm">{{ $booking->getTypeLabel() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $booking->scheduled_date->format('M d, Y') }}
                        @if($booking->scheduled_time)
                        · {{ \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') }}
                        @endif
                    </p>
                    @if($booking->parishioner)
                    <a href="{{ route('admin.parishioners.show', $booking->parishioner) }}"
                       class="text-xs text-blue-600 hover:underline font-medium mt-0.5 block">
                        {{ $booking->parishioner->full_name }}
                    </a>
                    @endif
                    <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $booking->reference_number }}</p>
                </div>
                <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800">
                        {{ $booking->getStatusLabel() }}
                    </span>
                    <span class="text-xs font-semibold text-gray-700">₱{{ number_format($booking->service_fee, 2) }}</span>
                </div>
            </div>
            <div class="flex justify-end pt-2 border-t border-gray-100">
                <a href="{{ route('admin.bookings.show', $booking) }}" class="action-btn action-btn-view">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    View Details
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-400">No bookings found.</div>
        @endforelse
        @if($bookings->hasPages())
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">{{ $bookings->links() }}</div>
        @endif
    </div>

    {{-- ── DESKTOP TABLE ── --}}
    <div id="bookings-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
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
                        $statusColors = ['pending'=>'amber','confirmed'=>'green','completed'=>'blue','cancelled'=>'red'];
                        $color = $statusColors[$booking->status] ?? 'gray';
                        $isPending = $booking->status === 'pending';
                    @endphp
                    <tr class="hover:bg-gray-50 {{ $isPending ? 'bg-amber-50' : '' }}" style="{{ $isPending ? 'border-left:4px solid #f59e0b;' : '' }}">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $booking->reference_number }}</td>
                        <td class="px-4 py-3">
                            @if($booking->parishioner)
                            <a href="{{ route('admin.parishioners.show', $booking->parishioner) }}" class="font-medium text-gray-900 hover:text-blue-700">
                                {{ $booking->parishioner->full_name }}
                            </a>
                            @else
                            <span class="text-gray-400 text-xs">Walk-in</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $booking->getTypeLabel() }}</td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $booking->scheduled_date->format('M d, Y') }}
                            @if($booking->scheduled_time)
                            <span class="text-gray-400 text-xs"> {{ \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">₱{{ number_format($booking->service_fee, 2) }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusConfig = [
                                    'pending'   => ['bg'=>'bg-amber-100',  'text'=>'text-amber-800',  'dot'=>'bg-amber-500'],
                                    'confirmed' => ['bg'=>'bg-green-100',  'text'=>'text-green-800',  'dot'=>'bg-green-500'],
                                    'completed' => ['bg'=>'bg-blue-100',   'text'=>'text-blue-800',   'dot'=>'bg-blue-500'],
                                    'cancelled' => ['bg'=>'bg-red-100',    'text'=>'text-red-800',    'dot'=>'bg-red-500'],
                                ];
                                $sc = $statusConfig[$booking->status] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-700','dot'=>'bg-gray-400'];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc['bg'] }} {{ $sc['text'] }} whitespace-nowrap">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $sc['dot'] }}"></span>
                                {{ $booking->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.bookings.show', $booking) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold transition whitespace-nowrap">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $bookings->links() }}</div>
        @endif
    </div>

</div>
@endsection
