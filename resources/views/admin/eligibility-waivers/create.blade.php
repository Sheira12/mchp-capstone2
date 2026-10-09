@extends('layouts.app')
@section('title', 'Grant Waiver')
@section('page-title', 'Grant Eligibility Waiver')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.eligibility-waivers.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Grant Eligibility Waiver</h1>
    </div>

    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 mb-5 text-sm text-amber-800">
        <p class="font-bold mb-1">⚠ This action is permanent and cannot be undone.</p>
        <p>Waivers are logged to the audit trail with your name, the time, and your reason. Granting a waiver allows the parishioner to bypass this rule for booking.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Parishioner</p>
        <p class="text-lg font-bold text-gray-900">{{ $parishioner->full_name }}</p>
        <p class="text-sm text-gray-500">{{ $parishioner->barangay }}, {{ $parishioner->city }}</p>

        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mt-4 mb-1">Rule to Waive</p>
        <p class="font-bold text-gray-900">{{ $rule->name }}</p>
        <p class="text-xs text-gray-500">{{ $rule->service?->name }} — {{ $rule->getCriterionLabel() }}</p>
        @if($rule->is_required)
        <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Required Rule</span>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.eligibility-waivers.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="parishioner_id" value="{{ $parishioner->id }}">
            <input type="hidden" name="eligibility_rule_id" value="{{ $rule->id }}">

            <div>
                <label class="form-label">Reason for Waiver <span class="text-red-500">*</span></label>
                <textarea name="reason" rows="3" required minlength="10"
                          class="form-input w-full @error('reason') border-red-400 @enderror"
                          placeholder="Explain clearly why this rule is being waived for this parishioner…">{{ old('reason') }}</textarea>
                @error('reason')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Expiry Date (optional)</label>
                <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="form-input w-full">
                <p class="text-xs text-gray-400 mt-1">Leave blank for a permanent waiver. Set a date if the waiver is only valid temporarily.</p>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="btn-danger">Grant Waiver (Permanent Action)</button>
                <a href="{{ url()->previous() }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
