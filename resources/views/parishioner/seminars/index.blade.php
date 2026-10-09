@extends('layouts.portal')
@section('title', 'Upcoming Seminars')

@section('content')
<div class="space-y-5 max-w-3xl w-full">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Upcoming Seminars</h1>
            <p class="text-sm text-gray-500 mt-0.5">Register for required seminars to qualify for parish services.</p>
        </div>
        <a href="{{ route('parishioner.seminars.history') }}" class="text-sm text-blue-600 hover:underline font-semibold">My History →</a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('info'))
    <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('info') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label">Filter by Service</label>
            <select name="service" class="form-select">
                <option value="">All Seminars</option>
                @foreach($services as $svc)
                <option value="{{ $svc->slug }}" @selected(request('service') === $svc->slug)>{{ $svc->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        <a href="{{ route('parishioner.seminars.index') }}" class="btn-secondary text-sm">Clear</a>
    </form>

    @if($seminars->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <p class="font-medium">No upcoming seminars scheduled.</p>
        <p class="text-sm mt-1">Check back later or contact the parish office.</p>
    </div>
    @else

    <div class="space-y-4">
        @foreach($seminars as $sem)
        @php
            $myStatus  = $myRegs[$sem->id] ?? null;
            $spots     = $sem->spotsRemaining();
            $isRegistered = in_array($myStatus, ['registered','attended']);
        @endphp

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                                {{ $sem->service?->name }}
                            </span>
                            @if($spots <= 5 && $spots > 0)
                            <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">
                                Only {{ $spots }} spot{{ $spots === 1 ? '' : 's' }} left!
                            </span>
                            @elseif($spots === 0)
                            <span class="text-xs font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">Full</span>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-gray-900">{{ $sem->title }}</h3>

                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $sem->scheduled_at->format('F d, Y') }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $sem->scheduled_at->format('g:i A') }}
                            </div>
                            @if($sem->venue)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $sem->venue }}
                            </div>
                            @endif
                            @if($sem->speaker)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{ $sem->speaker }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        @if($myStatus === 'attended')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Completed
                        </span>

                        @elseif($myStatus === 'registered')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700 border border-blue-200">
                            ✓ Registered
                        </span>
                        <form method="POST" action="{{ route('parishioner.seminars.cancel', $sem) }}">
                            @csrf
                            <button type="submit" class="text-xs text-red-500 hover:underline"
                                    onclick="return confirm('Cancel your registration?')">Cancel</button>
                        </form>

                        @elseif($spots > 0)
                        <form method="POST" action="{{ route('parishioner.seminars.register', $sem) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Register
                            </button>
                        </form>
                        <p class="text-xs text-gray-400 text-right">{{ $spots }} of {{ $sem->capacity }} spots available</p>

                        @else
                        <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Fully Booked</span>
                        @endif
                    </div>
                </div>

                @if($sem->notes)
                <p class="mt-3 text-xs text-gray-500 bg-gray-50 rounded-lg px-3 py-2 border border-gray-100">{{ $sem->notes }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div>{{ $seminars->links() }}</div>
    @endif

</div>
@endsection
