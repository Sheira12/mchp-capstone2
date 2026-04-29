@extends('layouts.public')

@section('title', 'Services & Sacraments')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900">Parish Services</h1>
        <p class="text-gray-500 mt-3 text-lg">Sacraments, blessings, seminars, and more</p>
    </div>

    @foreach($services as $category => $categoryServices)
    <div class="mb-12" id="{{ Str::slug($category) }}">
        <h2 class="text-2xl font-bold text-blue-900 mb-6 pb-2 border-b-2 border-blue-100">{{ $category }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($categoryServices as $service)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <h3 class="font-bold text-gray-900">{{ $service->name }}</h3>
                    @if($service->fee > 0)
                    <span class="text-sm font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded-full">₱{{ number_format($service->fee, 0) }}</span>
                    @else
                    <span class="text-sm text-gray-400">Free</span>
                    @endif
                </div>
                @if($service->description)
                <p class="text-sm text-gray-600 mb-4">{{ $service->description }}</p>
                @endif
                @if($service->requirements && count($service->requirements))
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Requirements</p>
                    <ul class="space-y-1">
                        @foreach($service->requirements as $req)
                        <li class="flex items-start gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $req }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if($service->is_bookable)
                <a href="{{ route('register') }}" class="btn-primary text-sm w-full text-center block">Book This Service</a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
