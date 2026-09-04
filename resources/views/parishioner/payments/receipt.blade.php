@extends('layouts.portal')
@section('title', 'Payment Receipt')

@section('content')
<div class="max-w-lg mx-auto space-y-6 py-4">

    {{-- Success header --}}
    <div class="text-center">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-gray-900">Payment Confirmed!</h1>
        <p class="text-gray-500 mt-1">Your payment has been successfully processed.</p>
    </div>

    {{-- Receipt Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-lg overflow-hidden">

        {{-- Receipt header --}}
        <div class="bg-gradient-to-br from-blue-700 to-indigo-800 px-6 py-5 text-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-blue-200 text-xs font-semibold uppercase tracking-wider">Official Receipt</p>
                    <p class="text-white font-bold text-lg">{{ config('parish.name') }}</p>
                    <p class="text-blue-200 text-xs">{{ config('parish.address') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-blue-200 text-xs">Receipt No.</p>
                    <p class="font-mono font-bold text-sm">{{ $payment->receipt_number }}</p>
                </div>
            </div>
            <div class="bg-white bg-opacity-10 rounded-xl p-4 text-center">
                <p class="text-blue-200 text-xs mb-1">Amount Paid</p>
                <p class="text-4xl font-extrabold">₱{{ number_format($payment->amount, 2) }}</p>
                <div class="flex items-center justify-center gap-2 mt-2">
                    @php
                        $methodIcons = ['gcash'=>'💚','maya'=>'💙','cash'=>'💵','bank'=>'🏦'];
                        $methodLabels = ['gcash'=>'GCash','maya'=>'Maya','cash'=>'Cash','bank'=>'Bank Transfer'];
                    @endphp
                    <span class="text-sm font-semibold text-blue-100">
                        {{ $methodIcons[$payment->payment_method] ?? '💳' }}
                        {{ $methodLabels[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
                    </span>
                    <span class="w-1 h-1 rounded-full bg-blue-300"></span>
                    <span class="text-sm text-blue-200">
                        {{ $payment->paid_at?->format('M d, Y g:i A') ?? now()->format('M d, Y') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Receipt body --}}
        <div class="px-6 py-5 space-y-4">

            {{-- Payment details --}}
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Transaction Type</span>
                    @php $badge = $payment->transaction_type_badge; @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                        {{ $badge['color'] === 'green' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $badge['label'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Reference Number</span>
                    <span class="font-mono text-sm font-bold text-gray-800">{{ $payment->reference_number }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Payment Status</span>
                    <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        Paid
                    </span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Payment Method</span>
                    <span class="text-sm font-semibold text-gray-800">{{ \App\Models\Payment::METHODS[$payment->payment_method] ?? ucfirst($payment->payment_method) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-500">Parishioner</span>
                    <span class="text-sm font-semibold text-gray-800">{{ $payment->parishioner->full_name }}</span>
                </div>
            </div>

            {{-- Booking details --}}
            @if($payment->booking)
            <div class="bg-blue-50 rounded-xl p-4 space-y-2">
                <p class="text-xs font-bold text-blue-600 uppercase tracking-wide mb-2">Booking Details</p>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Service</span>
                    <span class="font-semibold text-gray-800">{{ $payment->booking->getTypeLabel() }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Scheduled Date</span>
                    <span class="font-semibold text-gray-800">{{ $payment->booking->scheduled_date->format('F d, Y') }}</span>
                </div>
                @if($payment->booking->scheduled_time)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Time</span>
                    <span class="font-semibold text-gray-800">{{ \Carbon\Carbon::parse($payment->booking->scheduled_time)->format('g:i A') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Booking Reference</span>
                    <span class="font-mono text-xs font-bold text-gray-700">{{ $payment->booking->reference_number }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Booking Status</span>
                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-0.5 rounded-full
                        {{ $payment->booking->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $payment->booking->getStatusLabel() }}
                    </span>
                </div>
            </div>
            @endif

            {{-- Amount breakdown --}}
            <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-500">Service Fee</span>
                    <span class="font-semibold">₱{{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm font-bold border-t border-gray-200 pt-2 mt-2">
                    <span class="text-gray-800">Total Paid</span>
                    <span class="text-green-700 text-base">₱{{ number_format($payment->amount, 2) }}</span>
                </div>
            </div>

            {{-- Email notice --}}
            <div class="flex items-start gap-3 bg-blue-50 rounded-xl p-3">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <p class="text-xs text-blue-700">
                    A receipt has been sent to your email address. Please keep this for your records.
                </p>
            </div>

        </div>

        {{-- Receipt footer --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400">{{ config('parish.name') }}</p>
            <p class="text-xs text-gray-400">{{ config('parish.address') }} · {{ config('parish.phone') }}</p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <a href="{{ route('parishioner.payments.index') }}"
           class="flex items-center justify-center gap-2 bg-white border border-gray-200 text-gray-700 font-semibold text-sm py-3 rounded-xl hover:bg-gray-50 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Payment History
        </a>
        <a href="{{ route('parishioner.dashboard') }}"
           class="flex items-center justify-center gap-2 bg-blue-600 text-white font-bold text-sm py-3 rounded-xl hover:bg-blue-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
    </div>

    {{-- Download Official PDF Receipt --}}
    <a href="{{ route('parishioner.payments.receipt-pdf', $payment) }}"
       class="flex items-center justify-center gap-2 text-white font-bold text-sm py-3.5 rounded-xl transition shadow-lg hover:shadow-xl"
       style="background:linear-gradient(135deg,#1F3A5F,#2d5282);"
       onmouseover="this.style.background='linear-gradient(135deg,#162d4a,#1e3a5f)';"
       onmouseout="this.style.background='linear-gradient(135deg,#1F3A5F,#2d5282)';">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Download Official Receipt (PDF)
    </a>

    {{-- Print button --}}
    <button onclick="window.print()"
            class="w-full flex items-center justify-center gap-2 text-gray-500 text-sm hover:text-gray-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Print Receipt
    </button>

</div>
@endsection
