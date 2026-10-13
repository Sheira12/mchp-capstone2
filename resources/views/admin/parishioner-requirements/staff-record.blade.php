@extends('layouts.app')
@section('title', 'Record Requirement — ' . $parishioner->full_name)
@section('page-title', 'Record Requirement for Parishioner')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.parishioners.show', $parishioner) }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Record Requirement</h1>
            <p class="text-sm text-gray-500">For: <strong>{{ $parishioner->full_name }}</strong></p>
        </div>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 mb-5 text-sm text-amber-800">
        Staff-recorded requirements are automatically <strong>verified</strong> — no approval step needed.
        Use this when the parish itself hosted the seminar or has the original document on file.
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm mb-4">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.parishioner-requirements.staff-record') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="parishioner_id" value="{{ $parishioner->id }}">

            <div>
                <label class="form-label">Service & Requirement <span class="text-red-500">*</span></label>
                <select name="service_requirement_id" required class="form-select w-full @error('service_requirement_id') border-red-400 @enderror">
                    <option value="">Select a requirement…</option>
                    @foreach($services as $svc)
                    @if($svc->serviceRequirements->isNotEmpty())
                    <optgroup label="{{ $svc->name }}">
                        @foreach($svc->serviceRequirements as $req)
                        @php $isVerified = in_array($req->id, $existing); @endphp
                        <option value="{{ $req->id }}"
                                {{ $isVerified ? 'disabled' : '' }}
                                @selected(old('service_requirement_id') == $req->id)>
                            {{ $req->name }}
                            {{ $isVerified ? '(already verified)' : '' }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endif
                    @endforeach
                </select>
                @error('service_requirement_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date Completed <span class="text-red-500">*</span></label>
                    <input type="date" name="date_completed" required max="{{ date('Y-m-d') }}"
                           value="{{ old('date_completed') }}"
                           class="form-input w-full @error('date_completed') border-red-400 @enderror">
                    @error('date_completed')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Venue / Organizer <span class="text-red-500">*</span></label>
                    <input type="text" name="venue_or_organizer" required
                           value="{{ old('venue_or_organizer', config('parish.name')) }}"
                           class="form-input w-full @error('venue_or_organizer') border-red-400 @enderror">
                    @error('venue_or_organizer')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Internal Notes (optional)</label>
                <textarea name="notes" rows="2" class="form-input w-full"
                          placeholder="Staff notes, e.g. 'Attended parish seminar on March 12, 2026'…">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="btn-primary">Record & Verify</button>
                <a href="{{ route('admin.parishioners.show', $parishioner) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
