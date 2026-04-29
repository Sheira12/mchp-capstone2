@extends('layouts.portal')

@section('title', 'Payment Successful')
@section('page-title', 'Payment Successful')

@section('content')
<div class="py-12 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center max-w-md w-full">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
        <p class="text-gray-500 mb-2">Your payment has been processed successfully.</p>
        @if($ref)
            <p class="text-sm text-gray-400 mb-6">Reference: <span class="font-mono font-medium text-gray-700">{{ $ref }}</span></p>
        @endif
        <div class="flex flex-col gap-3">
            <a href="{{ route('parishioner.payments.index') }}" class="btn-primary">View Payment History</a>
            <a href="{{ route('parishioner.dashboard') }}" class="btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</div>
@endsection
