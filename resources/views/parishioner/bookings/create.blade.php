@extends('layouts.portal')

@section('title', 'Book a Service')

@section('content')
<div class="space-y-6 max-w-3xl">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.bookings.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Book a Service</h1>
            <p class="text-sm text-gray-500">Submit a booking request to the parish office</p>
        </div>
    </div>

    <form action="{{ route('parishioner.bookings.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Step 1: Service Selection --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">1</div>
                    <h2 class="font-bold text-gray-900">Select a Service</h2>
                </div>
            </div>
            <div class="p-6">
                @if($services->isEmpty())
                <p class="text-gray-400 text-sm">No bookable services available at this time.</p>
                @else
                @foreach($services as $category => $categoryServices)
                <div class="mb-6 last:mb-0">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">{{ $category }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($categoryServices as $service)
                        <label class="relative flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all hover:border-blue-300 hover:bg-blue-50 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="booking_type" value="{{ $service->slug }}"
                                   class="mt-0.5 text-blue-600 focus:ring-blue-500" required>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-sm text-gray-900">{{ $service->name }}</p>
                                @if($service->description)
                                <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $service->description }}</p>
                                @endif
                                <div class="mt-2">
                                    @if($service->fee > 0)
                                    <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                        ₱{{ number_format($service->fee, 0) }}
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                        Free / Donation
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
                @endif
                @error('booking_type')<p class="form-error mt-2">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Step 2: Schedule --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">2</div>
                    <h2 class="font-bold text-gray-900">Preferred Schedule</h2>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="form-label">Preferred Date <span class="text-red-500">*</span></label>
                        <input type="date" name="scheduled_date" value="{{ old('scheduled_date') }}"
                               min="{{ now()->addDay()->format('Y-m-d') }}"
                               required class="form-input w-full @error('scheduled_date') border-red-400 @enderror">
                        @error('scheduled_date')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="form-label">Preferred Time <span class="text-gray-400 text-xs">(optional)</span></label>
                        <input type="time" name="scheduled_time" value="{{ old('scheduled_time') }}" class="form-input w-full">
                        @error('scheduled_time')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Address <span class="text-gray-400 text-xs">(for home/car/business blessings)</span></label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               class="form-input w-full" placeholder="Complete address where service will be performed">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="form-label">Additional Notes <span class="text-gray-400 text-xs">(optional)</span></label>
                        <textarea name="notes" rows="3" class="form-input w-full"
                                  placeholder="Any special requests, names of persons involved, or other information...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notice --}}
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-amber-800">
                <p class="font-bold mb-1">Important Reminders</p>
                <ul class="space-y-1 list-disc list-inside text-amber-700">
                    <li>Your booking is subject to approval by the parish office.</li>
                    <li>You will receive an email confirmation once approved.</li>
                    <li>Please ensure you have completed required seminars before booking sacraments.</li>
                    <li>Payment can be made via GCash, Maya, or cash at the parish office.</li>
                </ul>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-8 py-3 rounded-xl hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Submit Booking Request
            </button>
            <a href="{{ route('parishioner.bookings.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
