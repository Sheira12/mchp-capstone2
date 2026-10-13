@extends('layouts.app')
@section('title', 'Review Declaration')
@section('page-title', 'Review Requirement Declaration')

@section('content')
<div class="py-6 max-w-3xl space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.parishioner-requirements.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Review Declaration</h1>
            <p class="text-sm text-gray-500">{{ $parishionerRequirement->parishioner?->full_name }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Declaration details --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <p class="font-bold text-gray-900">{{ $parishionerRequirement->requirement?->name }}</p>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $parishionerRequirement->requirement?->service?->name }} —
                {{ $parishionerRequirement->getSourceLabel() }}
            </p>
        </div>
        <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Parishioner</p>
                <p class="font-semibold text-gray-900">{{ $parishionerRequirement->parishioner?->full_name }}</p>
                <p class="text-gray-500">{{ $parishionerRequirement->parishioner?->contact_number }}</p>
                <a href="{{ route('admin.parishioners.show', $parishionerRequirement->parishioner) }}"
                   class="text-xs text-blue-600 hover:underline mt-1 inline-block">View full profile →</a>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Date Completed</p>
                <p class="text-gray-900">{{ $parishionerRequirement->date_completed?->format('F d, Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Venue / Organizer</p>
                <p class="text-gray-900">{{ $parishionerRequirement->venue_or_organizer ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Submitted</p>
                <p class="text-gray-900">{{ $parishionerRequirement->created_at->format('M d, Y g:i A') }}</p>
            </div>
            @if($parishionerRequirement->notes)
            <div class="sm:col-span-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Notes from Parishioner</p>
                <p class="text-gray-700 bg-gray-50 rounded-lg px-3 py-2 border border-gray-100">{{ $parishionerRequirement->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Proof --}}
        @if($parishionerRequirement->proof_path)
        <div class="px-5 pb-5 border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Submitted Proof</p>
            @if($parishionerRequirement->proofIsImage())
            <a href="{{ $parishionerRequirement->proofUrl() }}" target="_blank">
                <img src="{{ $parishionerRequirement->proofUrl() }}" alt="Proof"
                     class="max-h-64 rounded-xl border border-gray-200 object-contain hover:opacity-90 transition cursor-zoom-in">
            </a>
            @else
            <a href="{{ $parishionerRequirement->proofUrl() }}" target="_blank"
               class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 border border-blue-200 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                View {{ strtoupper($parishionerRequirement->proofExtension()) }} Document
            </a>
            @endif
        </div>
        @else
        <div class="px-5 pb-4 border-t border-gray-100 pt-3">
            <p class="text-xs text-gray-400 italic">No proof file submitted.</p>
        </div>
        @endif
    </div>

    {{-- Previous rejection remark --}}
    @if($parishionerRequirement->status === 'rejected' && $parishionerRequirement->rejection_reason)
    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 text-sm text-red-800">
        <p class="font-bold mb-1">Previous rejection reason:</p>
        <p>{{ $parishionerRequirement->rejection_reason }}</p>
    </div>
    @endif

    {{-- Actions --}}
    @if($parishionerRequirement->status === 'pending')
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Approve --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="font-bold text-gray-900 mb-1">Approve Declaration</p>
            <p class="text-xs text-gray-500 mb-3">Mark this requirement as verified. The parishioner will be notified.</p>
            <form method="POST" action="{{ route('admin.parishioner-requirements.approve', $parishionerRequirement) }}">
                @csrf
                <textarea name="admin_note" rows="2" class="form-input w-full text-sm mb-3"
                          placeholder="Optional note for the parishioner…"></textarea>
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Approve & Verify
                </button>
            </form>
        </div>

        {{-- Reject --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <p class="font-bold text-gray-900 mb-1">Reject Declaration</p>
            <p class="text-xs text-gray-500 mb-3">Explain what needs to be corrected so the parishioner can resubmit.</p>
            <form method="POST" action="{{ route('admin.parishioner-requirements.reject', $parishionerRequirement) }}">
                @csrf
                <textarea name="rejection_reason" rows="2" required minlength="5"
                          class="form-input w-full text-sm mb-3 @error('rejection_reason') border-red-400 @enderror"
                          placeholder="Explain why this is rejected and what they need to fix…"></textarea>
                @error('rejection_reason')<p class="form-error text-xs mb-2">{{ $message }}</p>@enderror
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reject & Request Revision
                </button>
            </form>
        </div>
    </div>

    @elseif($parishionerRequirement->status === 'verified')
    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 text-sm text-green-800 flex items-center gap-2">
        <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        Verified by {{ $parishionerRequirement->verifiedBy?->name }} on {{ $parishionerRequirement->verified_at?->format('M d, Y g:i A') }}
    </div>
    @endif

</div>
@endsection
