@extends('layouts.public')

@section('title', 'Contact Us')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-4xl font-bold text-gray-900 mb-8">Contact Us</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {{-- Contact Info --}}
        <div>
            <div class="bg-blue-900 text-white rounded-2xl p-8 mb-6">
                <h2 class="text-xl font-bold mb-6">Parish Office</h2>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <div>
                            <p class="font-medium">Address</p>
                            <p class="text-blue-200 text-sm">{{ config('parish.address') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <div>
                            <p class="font-medium">Phone</p>
                            <p class="text-blue-200 text-sm">{{ config('parish.phone') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <div>
                            <p class="font-medium">Email</p>
                            <p class="text-blue-200 text-sm">{{ config('parish.email') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4">Office Hours</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Monday – Friday</span>
                        <span class="font-medium">8:00 AM – 5:00 PM</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Saturday</span>
                        <span class="font-medium">8:00 AM – 12:00 PM</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sunday</span>
                        <span class="font-medium">After morning Masses</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Inquiry Form --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Send an Inquiry</h2>

            <form method="POST" action="{{ route('contact.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Your Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Subject <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Message <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="5" required class="form-input w-full" placeholder="How can we help you?">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="w-full btn-primary py-2.5">Send Inquiry</button>
            </form>
        </div>
    </div>
</div>
@endsection
