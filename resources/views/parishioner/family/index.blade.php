@extends('layouts.portal')
@section('title', 'My Family')

@section('content')
<div class="space-y-6 py-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Family</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your family record and members linked to your account.</p>
    </div>

    @if(session('success'))
    <div class="flash flash-success">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flash flash-error">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        {{ session('error') }}
    </div>
    @endif
    @if(session('warning'))
    <div class="flash" style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        {{ session('warning') }}
    </div>
    @endif

    @if(!$family)
    {{-- ── NO FAMILY YET ── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center max-w-lg mx-auto">
        <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-900 mb-2">No Family Record Yet</h2>
        <p class="text-sm text-gray-500 mb-6">Create a family profile to link your spouse, children, and relatives. This helps the parish manage your records together.</p>

        <form method="POST" action="{{ route('parishioner.family.store') }}" class="text-left space-y-4">
            @csrf
            <div>
                <label class="form-label">Family Name <span class="text-red-500">*</span></label>
                <input type="text" name="family_name" value="{{ old('family_name', $parishioner->last_name . ' Family') }}"
                       required class="form-input w-full" placeholder="e.g., Santos Family">
                @error('family_name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Barangay</label>
                    <input type="text" name="barangay" value="{{ old('barangay', $parishioner->barangay) }}" class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">City / Municipality</label>
                    <input type="text" name="city" value="{{ old('city', $parishioner->city) }}" class="form-input w-full">
                </div>
            </div>
            <div>
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number', $parishioner->contact_number) }}" class="form-input w-full">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition">
                Create Family Profile
            </button>
        </form>
    </div>

    @else
    {{-- ── FAMILY EXISTS ── --}}
    {{-- Family info card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $family->family_name }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if($family->barangay) Brgy. {{ $family->barangay }}, @endif
                    {{ $family->city }}{{ $family->province ? ', ' . $family->province : '' }}
                </p>
                @if($parishioner->is_head_of_family)
                <span class="inline-flex items-center gap-1 mt-2 text-xs font-bold bg-blue-100 text-blue-800 px-2.5 py-0.5 rounded-full">
                    👑 Head of Family
                </span>
                @else
                <span class="inline-flex items-center gap-1 mt-2 text-xs font-bold bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full">
                    {{ $parishioner->relationship_to_head ?? 'Member' }}
                </span>
                @endif
            </div>
            <div class="text-right">
                <p class="text-2xl font-extrabold text-blue-700">{{ $family->members->count() }}</p>
                <p class="text-xs text-gray-400">members</p>
            </div>
        </div>
    </div>

    {{-- Members list --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-900">Family Members</h3>
            <span class="text-xs text-gray-400">{{ $family->members->count() }} total</span>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($family->members as $member)
            <div class="flex items-center gap-4 px-6 py-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-700 flex-shrink-0">
                    {{ substr($member->first_name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-gray-900 truncate">
                        {{ $member->full_name }}
                        @if($member->id === $parishioner->id)
                        <span class="text-xs text-blue-500 font-normal">(you)</span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ $member->relationship_to_head ?? 'Member' }}
                        @if($member->birthdate) · Age {{ $member->age }} @endif
                    </p>
                </div>
                @if($parishioner->is_head_of_family && $member->id !== $parishioner->id)
                <form method="POST" action="{{ route('parishioner.family.remove-member', $member) }}"
                      onsubmit="return confirm('Remove {{ $member->full_name }} from your family?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold transition">
                        Remove
                    </button>
                </form>
                @endif
            </div>
            @empty
            <p class="px-6 py-8 text-center text-sm text-gray-400">No members yet. Add your family members below.</p>
            @endforelse
        </div>
    </div>

    {{-- Add member form (head only) --}}
    @if($parishioner->is_head_of_family)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-bold text-gray-900 mb-4">Add a Family Member</h3>
        <p class="text-xs text-gray-400 mb-4">
            If this person already has a parishioner account, we'll link them automatically by name and birthdate.
            Otherwise a new profile will be created.
        </p>
        <form method="POST" action="{{ route('parishioner.family.add-member') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required class="form-input w-full">
                    @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" value="{{ old('middle_name') }}" class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required class="form-input w-full">
                    @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Relationship <span class="text-red-500">*</span></label>
                    <select name="relationship_to_head" required class="form-select w-full">
                        <option value="">Select…</option>
                        @foreach(['Spouse','Son','Daughter','Father','Mother','Brother','Sister','Grandfather','Grandmother','Grandchild','Nephew','Niece','Other'] as $rel)
                        <option value="{{ $rel }}" {{ old('relationship_to_head')===$rel ? 'selected' : '' }}>{{ $rel }}</option>
                        @endforeach
                    </select>
                    @error('relationship_to_head')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Birthdate</label>
                    <input type="date" name="birthdate" value="{{ old('birthdate') }}" class="form-input w-full"
                           max="{{ now()->subDay()->toDateString() }}">
                </div>
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select w-full">
                        <option value="">Select…</option>
                        <option value="male"   {{ old('gender')==='male'   ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender')==='female' ? 'selected' : '' }}>Female</option>
                        <option value="other"  {{ old('gender')==='other'  ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="form-input w-full sm:w-1/2"
                       placeholder="09XX-XXX-XXXX (optional)">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2.5 rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Member
            </button>
        </form>
    </div>
    @endif

    @endif
</div>
@endsection
