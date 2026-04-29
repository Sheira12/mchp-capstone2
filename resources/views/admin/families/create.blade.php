@extends('layouts.app')

@section('title', 'New Family')
@section('page-title', 'New Family')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.families.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="form-label">Family Name <span class="text-red-500">*</span></label>
                <input type="text" name="family_name" value="{{ old('family_name') }}" required
                       class="form-input w-full @error('family_name') border-red-400 @enderror"
                       placeholder="e.g. Santos Family">
                @error('family_name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Address</label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="form-input w-full" placeholder="Street address">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Barangay</label>
                    <input type="text" name="barangay" value="{{ old('barangay') }}"
                           class="form-input w-full" placeholder="Barangay">
                </div>
                <div>
                    <label class="form-label">City / Municipality</label>
                    <input type="text" name="city" value="{{ old('city', 'Cabuyao') }}"
                           class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Province</label>
                    <input type="text" name="province" value="{{ old('province', 'Laguna') }}"
                           class="form-input w-full">
                </div>
            </div>

            <div>
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number') }}"
                       class="form-input w-full" placeholder="09XX-XXX-XXXX">
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input w-full"
                          placeholder="Optional notes about this family">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Create Family</button>
                <a href="{{ route('admin.families.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
