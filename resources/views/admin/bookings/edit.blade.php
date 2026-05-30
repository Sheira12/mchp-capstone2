@extends('layouts.app')
@section('title', 'Edit Booking — ' . $booking->reference_number)
@section('page-title', 'Edit Booking')

@section('content')
<div class="py-6 max-w-3xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.bookings.show', $booking) }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Booking</h1>
            <p class="text-sm text-gray-500 font-mono">{{ $booking->reference_number }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        {{-- Parishioner (read-only) --}}
        <div class="mb-5 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                {{ substr($booking->parishioner->first_name, 0, 1) }}
            </div>
            <div>
                <p class="font-bold text-blue-900">{{ $booking->parishioner->full_name }}</p>
                <p class="text-xs text-blue-500">{{ $booking->parishioner->contact_number ?? 'No contact' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div class="sm:col-span-2">
                    <label class="form-label">Service Type <span class="text-red-500">*</span></label>
                    <select name="booking_type" required class="form-select w-full @error('booking_type') border-red-400 @enderror">
                        @foreach(\App\Models\Booking::TYPES as $val => $label)
                        <option value="{{ $val }}" @selected(old('booking_type', $booking->booking_type) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('booking_type')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Scheduled Date <span class="text-red-500">*</span></label>
                    <input type="date" name="scheduled_date"
                           value="{{ old('scheduled_date', $booking->scheduled_date->format('Y-m-d')) }}"
                           required class="form-input w-full @error('scheduled_date') border-red-400 @enderror">
                    @error('scheduled_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Scheduled Time</label>
                    <input type="time" name="scheduled_time"
                           value="{{ old('scheduled_time', $booking->scheduled_time) }}"
                           class="form-input w-full">
                </div>

                <div>
                    <label class="form-label">Service Fee (₱)</label>
                    <input type="number" name="service_fee" step="0.01" min="0"
                           value="{{ old('service_fee', $booking->service_fee) }}"
                           class="form-input w-full">
                </div>

                <div>
                    <label class="form-label">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="form-select w-full @error('status') border-red-400 @enderror">
                        @foreach(\App\Models\Booking::STATUSES as $val => $label)
                        <option value="{{ $val }}" @selected(old('status', $booking->status) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Address <span class="text-gray-400 text-xs">(for blessings)</span></label>
                    <input type="text" name="address"
                           value="{{ old('address', $booking->address) }}"
                           class="form-input w-full" placeholder="Service location">
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Parishioner Notes</label>
                    <textarea name="notes" rows="2" class="form-input w-full"
                              placeholder="Notes from parishioner…">{{ old('notes', $booking->notes) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Admin Notes</label>
                    <textarea name="admin_notes" rows="2" class="form-input w-full"
                              placeholder="Internal admin notes…">{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                </div>

            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn-secondary">Cancel</a>
                <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST"
                      class="ml-auto" onsubmit="return confirm('Permanently delete this booking?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50">Delete</button>
                </form>
            </div>
        </form>
    </div>

</div>
@endsection
