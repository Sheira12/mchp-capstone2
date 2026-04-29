@extends('layouts.public')

@section('title', 'Document Verification')

@section('content')
<div class="min-h-screen bg-gray-50 py-16">
    <div class="max-w-lg mx-auto px-4">

        <div class="text-center mb-8">
            <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo" class="w-16 h-16 rounded-full mx-auto mb-4 object-cover">
            <h1 class="text-2xl font-bold text-gray-900">Document Verification</h1>
            <p class="text-gray-500 mt-1">Mary Help of Christians Parish</p>
        </div>

        @if($valid)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Valid Header --}}
            <div class="bg-green-500 text-white px-6 py-5 flex items-center gap-3">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="font-bold text-lg">Document Verified</p>
                    <p class="text-green-100 text-sm">This document is authentic and valid</p>
                </div>
            </div>

            {{-- Document Details --}}
            <div class="p-6">
                <dl class="space-y-4">
                    @foreach($data as $key => $value)
                    @if(!in_array($key, ['scan_count', 'last_scanned']))
                    <div class="flex justify-between items-start border-b border-gray-50 pb-3 last:border-0 last:pb-0">
                        <dt class="text-sm text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</dt>
                        <dd class="text-sm font-medium text-gray-900 text-right max-w-xs">{{ $value }}</dd>
                    </div>
                    @endif
                    @endforeach
                </dl>

                <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-400">
                        Scanned {{ $qrCode->scan_count }} time(s)
                        @if($qrCode->last_scanned_at)
                        · Last scanned {{ $qrCode->last_scanned_at->diffForHumans() }}
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Verified by Mary Help of Christians Parish System
                    </p>
                </div>
            </div>
        </div>

        @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-red-500 text-white px-6 py-5 flex items-center gap-3">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="font-bold text-lg">Verification Failed</p>
                    <p class="text-red-100 text-sm">{{ $message ?? 'This document could not be verified.' }}</p>
                </div>
            </div>
            <div class="p-6 text-center">
                <p class="text-gray-600 text-sm mb-4">If you believe this is an error, please contact the parish office.</p>
                <a href="{{ route('contact') }}" class="btn-primary text-sm">Contact Parish Office</a>
            </div>
        </div>
        @endif

        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-sm text-blue-600 hover:underline">← Return to Parish Website</a>
        </div>

    </div>
</div>
@endsection
