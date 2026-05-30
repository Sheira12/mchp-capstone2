@extends('layouts.portal')

@section('title', 'My Payments')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Payment History</h1>
        <p class="text-sm text-gray-500 mt-1">View all your transactions and receipts</p>
    </div>

    @if($payments->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">No payments yet</h3>
        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">Your payment history will appear here once you make transactions for bookings or certificates.</p>
        <a href="{{ route('parishioner.bookings.index') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-blue-700 shadow-lg transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            View My Bookings
        </a>
    </div>
    @else

    {{-- Payments List --}}
    <div class="space-y-3">
        @foreach($payments as $payment)
        @php
            $statusColors = ['pending'=>'amber','paid'=>'green','refunded'=>'blue','voided'=>'red'];
            $sc = $statusColors[$payment->status] ?? 'gray';
            $methodIcons = [
                'gcash' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
                'paymaya' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
                'cash' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
            ];
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition overflow-hidden">
            <div class="h-1 bg-{{ $sc }}-400"></div>
            <div class="p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-{{ $sc }}-50 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-{{ $sc }}-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                {!! $methodIcons[$payment->payment_method] ?? $methodIcons['cash'] !!}
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-lg text-gray-900">₱{{ number_format($payment->amount, 2) }}</p>
                            <p class="text-sm text-gray-500 capitalize mt-0.5">{{ $payment->payment_method }} payment</p>
                            @if($payment->booking)
                            <p class="text-xs text-gray-400 mt-1">For: {{ $payment->booking->getTypeLabel() }}</p>
                            @endif
                            @if($payment->reference_number)
                            <p class="text-xs text-gray-400 font-mono mt-1">Ref: {{ $payment->reference_number }}</p>
                            @endif
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $payment->paid_at ? $payment->paid_at->format('M d, Y g:i A') : $payment->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                        <span class="badge badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                        @if($payment->status === 'paid')
                        <a href="{{ route('parishioner.payments.receipt', $payment) }}"
                           style="display:inline-flex;align-items:center;gap:4px;font-size:0.72rem;font-weight:700;color:#2563eb;text-decoration:none;background:#eff6ff;padding:3px 10px;border-radius:6px;border:1px solid #bfdbfe;"
                           onmouseover="this.style.background='#dbeafe';" onmouseout="this.style.background='#eff6ff';">
                            <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            View Receipt
                        </a>
                        @elseif($payment->status === 'pending' && $payment->booking)
                        <a href="{{ route('parishioner.payments.pay', $payment->booking) }}"
                           style="display:inline-flex;align-items:center;gap:4px;font-size:0.72rem;font-weight:700;color:#fff;text-decoration:none;background:#2563eb;padding:3px 10px;border-radius:6px;"
                           onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                            Pay Now
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div>{{ $payments->links() }}</div>

    @endif

    {{-- Help Card --}}
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 mb-1">Payment Methods</h3>
                <p class="text-sm text-gray-600 mb-3">We accept GCash, Maya (PayMaya), and cash payments at the parish office. All online payments are secure and encrypted.</p>
                <a href="{{ route('contact') }}" class="text-sm font-semibold text-blue-600 hover:underline">Contact us for payment concerns →</a>
            </div>
        </div>
    </div>
</div>
@endsection
