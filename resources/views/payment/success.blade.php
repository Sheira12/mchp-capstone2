@extends('layouts.public')
@section('title', 'Payment Successful')
@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-16">
    <div class="max-w-md mx-auto px-4 text-center">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
            <p class="text-gray-500 mb-6">Your payment has been processed. A receipt will be sent to your email.</p>
            @if(request('ref'))
            <p class="text-xs text-gray-400 mb-6 font-mono">Reference: {{ request('ref') }}</p>
            @endif
            <a href="{{ route('parishioner.dashboard') }}" class="btn-primary">Go to My Dashboard</a>
        </div>
    </div>
</div>
@endsection
