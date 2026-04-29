@extends('layouts.portal')

@section('title', 'Payment Failed')
@section('page-title', 'Payment Failed')

@section('content')
<div class="py-12 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center max-w-md w-full">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h2>
        <p class="text-gray-500 mb-2">Your payment could not be processed. Please try again.</p>
        @if($ref)
            <p class="text-sm text-gray-400 mb-6">Reference: <span class="font-mono font-medium text-gray-700">{{ $ref }}</span></p>
        @endif
        <div class="flex flex-col gap-3">
            <a href="{{ route('parishioner.bookings.index') }}" class="btn-primary">Try Again</a>
            <a href="{{ route('parishioner.dashboard') }}" class="btn-secondary">Back to Dashboard</a>
        </div>
        <p class="text-xs text-gray-400 mt-4">If you were charged, please contact the parish office.</p>
    </div>
</div>
@endsection
