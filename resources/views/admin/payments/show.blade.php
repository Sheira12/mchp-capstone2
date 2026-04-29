@extends('layouts.app')

@section('title', 'Payment Details')
@section('page-title', 'Payment Details')

@section('content')
<div class="py-6 space-y-5 max-w-2xl">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.payments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Payments</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        @php
            $statusColors = ['pending' => 'amber', 'paid' => 'green', 'refunded' => 'blue', 'voided' => 'red'];
            $sc = $statusColors[$payment->status] ?? 'gray';
        @endphp
        <div class="flex items-start justify-between mb-5">
            <div>
                <p class="font-mono text-sm text-gray-500 mb-1">{{ $payment->reference_number ?? 'No reference' }}</p>
                <h2 class="text-xl font-bold text-gray-900">₱{{ number_format($payment->amount, 2) }}</h2>
                <p class="text-gray-500 capitalize">{{ $payment->payment_method }} payment</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $sc }}-100 text-{{ $sc }}-800">
                {{ ucfirst($payment->status) }}
            </span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Parishioner</dt>
                <dd class="font-medium text-gray-900 mt-0.5">
                    @if($payment->parishioner)
                        <a href="{{ route('admin.parishioners.show', $payment->parishioner) }}" class="hover:text-blue-700">
                            {{ $payment->parishioner->full_name }}
                        </a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Receipt Number</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $payment->receipt_number ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Paid At</dt>
                <dd class="font-medium text-gray-900 mt-0.5">
                    {{ $payment->paid_at ? $payment->paid_at->format('F d, Y g:i A') : '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Created At</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $payment->created_at->format('F d, Y g:i A') }}</dd>
            </div>
            @if($payment->booking)
            <div>
                <dt class="text-gray-500">Linked Booking</dt>
                <dd class="font-medium text-gray-900 mt-0.5">
                    <a href="{{ route('admin.bookings.show', $payment->booking) }}" class="text-blue-600 hover:underline">
                        {{ $payment->booking->getTypeLabel() }} — {{ $payment->booking->scheduled_date->format('M d, Y') }}
                    </a>
                </dd>
            </div>
            @endif
            @if($payment->notes)
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Notes</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $payment->notes }}</dd>
            </div>
            @endif
        </dl>

        @if($payment->status === 'refunded')
        <div class="mt-4 pt-4 border-t border-gray-100 bg-blue-50 rounded-lg p-3">
            <p class="text-sm font-medium text-blue-800">Refunded</p>
            <p class="text-sm text-blue-700">{{ $payment->refund_reason }}</p>
            <p class="text-xs text-blue-500 mt-1">by {{ $payment->refundedBy?->name }} on {{ $payment->refunded_at?->format('M d, Y') }}</p>
        </div>
        @endif

        @if($payment->status === 'voided')
        <div class="mt-4 pt-4 border-t border-gray-100 bg-red-50 rounded-lg p-3">
            <p class="text-sm font-medium text-red-800">Voided</p>
            <p class="text-sm text-red-700">{{ $payment->void_reason }}</p>
            <p class="text-xs text-red-500 mt-1">by {{ $payment->voidedBy?->name }} on {{ $payment->voided_at?->format('M d, Y') }}</p>
        </div>
        @endif
    </div>

    {{-- Actions --}}
    @if($payment->status === 'paid')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
        <h3 class="font-semibold text-gray-800">Actions</h3>

        <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="form-label">Refund Reason</label>
                <input type="text" name="refund_reason" required class="form-input w-full" placeholder="Reason for refund…">
            </div>
            <button type="submit" class="btn-secondary text-sm"
                    onclick="return confirm('Refund this payment?')">Refund</button>
        </form>

        <form method="POST" action="{{ route('admin.payments.void', $payment) }}" class="flex gap-3 items-end">
            @csrf
            <div class="flex-1">
                <label class="form-label">Void Reason</label>
                <input type="text" name="void_reason" required class="form-input w-full" placeholder="Reason for voiding…">
            </div>
            <button type="submit" class="btn-danger text-sm"
                    onclick="return confirm('Void this payment?')">Void</button>
        </form>
    </div>
    @endif
</div>
@endsection
