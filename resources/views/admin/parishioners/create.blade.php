@extends('layouts.app')

@section('title', 'Add Parishioner')
@section('page-title', 'Add Parishioner')

@section('content')
<div class="py-6 max-w-4xl">

    <form action="{{ route('admin.parishioners.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Personal Information --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-5">Personal Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-input @error('first_name') border-red-400 @enderror" required>
                    @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="form-input @error('last_name') border-red-400 @enderror" required>
                    @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Suffix</label>
                    <input type="text" name="suffix" value="{{ old('suffix') }}" placeholder="Jr., Sr., III..." class="form-input">
                </div>
                <div>
                    <label class="form-label">Birthdate</label>
                    <input type="date" name="birthdate" value="{{ old('birthdate') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select...</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Civil Status</label>
                    <select name="civil_status" class="form-select">
                        <option value="">Select...</option>
                        @foreach(['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'separated' => 'Separated', 'annulled' => 'Annulled'] as $val => $label)
                        <option value="{{ $val }}" {{ old('civil_status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="+63 9XX XXX XXXX" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input">
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-5">Address</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label">Street Address</label>
                    <input type="text" name="address" value="{{ old('address') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Barangay</label>
                    <input type="text" name="barangay" value="{{ old('barangay', 'Niugan') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">City/Municipality</label>
                    <input type="text" name="city" value="{{ old('city', 'Cabuyao') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Province</label>
                    <input type="text" name="province" value="{{ old('province', 'Laguna') }}" class="form-input">
                </div>
            </div>
        </div>

        {{-- Family --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-5">Family Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Family</label>
                    <select name="family_id" class="form-select">
                        <option value="">No family / Create new</option>
                        @foreach($families as $family)
                        <option value="{{ $family->id }}" {{ old('family_id') == $family->id ? 'selected' : '' }}>{{ $family->family_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Relationship to Head</label>
                    <input type="text" name="relationship_to_head" value="{{ old('relationship_to_head') }}" placeholder="Spouse, Child, Parent..." class="form-input">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_head_of_family" id="is_head" value="1" {{ old('is_head_of_family') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                    <label for="is_head" class="text-sm text-gray-700">This person is the head of the family</label>
                </div>
            </div>
        </div>

        {{-- Photo --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-5">Photo</h2>
            <div class="flex items-center gap-4">
                <div id="photo-preview" class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <input type="file" name="photo" id="photo-input" accept="image/*" class="hidden" onchange="previewPhoto(this)">
                    <label for="photo-input" class="btn-secondary text-sm cursor-pointer">Choose Photo</label>
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG up to 2MB</p>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-3">Notes</h2>
            <textarea name="notes" rows="3" class="form-input w-full" placeholder="Additional notes...">{{ old('notes') }}</textarea>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Save Parishioner</button>
            <a href="{{ route('admin.parishioners.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('photo-preview');
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
