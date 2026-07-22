@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')

@section('content')
<div class="py-6 space-y-6">
    {{-- Summary stat cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Total Parishioners</p>
            <p class="text-3xl font-bold text-blue-700">{{ number_format($stats['total_parishioners']) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ number_format($stats['active_parishioners']) }} active</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Families</p>
            <p class="text-3xl font-bold text-indigo-600">{{ number_format($stats['total_families']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Total Bookings</p>
            <p class="text-3xl font-bold text-amber-600">{{ number_format($stats['total_bookings']) }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ number_format($stats['pending_bookings']) }} pending</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide mb-1">Total Revenue</p>
            <p class="text-3xl font-bold text-green-600">₱{{ number_format($stats['total_revenue'], 0) }}</p>
            <p class="text-xs text-red-400 mt-1">₱{{ number_format($stats['outstanding'], 0) }} outstanding</p>
        </div>
    </div>

    {{-- Report modules --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <a href="{{ route('admin.reports.parishioners') }}"
           class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:border-blue-200 transition group block">
            <div class="w-12 h-12 rounded-xl bg-blue-100 group-hover:bg-blue-200 flex items-center justify-center mb-4 transition">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-1">Parishioner Report</h3>
            <p class="text-sm text-gray-500">Demographics, gender breakdown, family stats, new registrations by date range.</p>
            <span class="inline-block mt-3 text-xs font-semibold text-blue-600">Generate Report →</span>
        </a>

        <a href="{{ route('admin.reports.payments') }}"
           class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:border-green-200 transition group block">
            <div class="w-12 h-12 rounded-xl bg-green-100 group-hover:bg-green-200 flex items-center justify-center mb-4 transition">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-1">Payment Report</h3>
            <p class="text-sm text-gray-500">Daily/monthly collections, payment methods, outstanding balances, refunds.</p>
            <span class="inline-block mt-3 text-xs font-semibold text-green-600">Generate Report →</span>
        </a>

        <a href="{{ route('admin.reports.bookings') }}"
           class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md hover:border-amber-200 transition group block">
            <div class="w-12 h-12 rounded-xl bg-amber-100 group-hover:bg-amber-200 flex items-center justify-center mb-4 transition">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-1">Booking Report</h3>
            <p class="text-sm text-gray-500">Booking counts by type and status, approval rates, date range summaries.</p>
            <span class="inline-block mt-3 text-xs font-semibold text-amber-600">Generate Report →</span>
        </a>
    </div>
</div>
@endsection
