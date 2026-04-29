@extends('layouts.public')
@section('title', 'Payment Failed')
@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-16">
    <div class="max-w-md mx-auto px-4 text-center">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
            <p class="text-gray-500 mb-6">Your payment could not be processed. Please try again or contact the parish office.</p>
            <div class="flex gap-3 justify-center">
                <a href="{{ route('parishioner.bookings.index') }}" class="btn-primary">Try Again</a>
                <a href="{{ route('contact') }}" class="btn-secondary">Contact Us</a>
            </div>
        </div>
    </div>
</div>
@endsection
