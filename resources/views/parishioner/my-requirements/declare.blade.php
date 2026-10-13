@extends('layouts.portal')
@section('title', 'Declare: ' . $serviceRequirement->name)

@section('content')
<div class="space-y-6 max-w-2xl w-full">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.my-requirements.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Declare Requirement</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $service?->name }} &mdash; <strong>{{ $serviceRequirement->name }}</strong>
            </p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm space-y-1">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- Info box --}}
    @php
        $typeLabel = match($serviceRequirement->eligibility_type ?? $serviceRequirement->type) {
            'seminar'          => 'Seminar / Catechesis',
            'sacrament_record' => 'Sacramental Record',
            default            => 'Document',
        };
        $typeIcon = match($serviceRequirement->eligibility_type ?? $serviceRequirement->type) {
            'seminar'          => '🎓',
            'sacrament_record' => '✝',
            default            => '📄',
        };
    @endphp

    <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 text-sm text-blue-800">
        <p class="font-bold mb-1">{{ $typeIcon }} {{ $typeLabel }}</p>
        @if($serviceRequirement->description)
        <p class="leading-relaxed">{{ $serviceRequirement->description }}</p>
        @endif
        @if($serviceRequirement->requires_proof)
        <p class="mt-2 font-semibold">📎 Proof of completion is required (e.g. certificate, receipt, confirmation slip).</p>
        @else
        <p class="mt-2 text-blue-600">No file upload required — just fill in the details below.</p>
        @endif
    </div>

    {{-- Existing declaration status --}}
    @if($existing)
    <div class="bg-{{ $existing->status === 'rejected' ? 'red' : 'amber' }}-50 border border-{{ $existing->status === 'rejected' ? 'red' : 'amber' }}-200 rounded-xl px-5 py-4 text-sm">
        @if($existing->status === 'rejected')
        <p class="font-bold text-red-800">Previous declaration was rejected.</p>
        <p class="text-red-700 mt-1">Reason: {{ $existing->rejection_reason ?? '—' }}</p>
        <p class="text-red-600 mt-1">Please correct and resubmit below.</p>
        @else
        <p class="font-semibold text-amber-800">You have a pending declaration for this requirement.</p>
        <p class="text-amber-700 mt-1">Submitted {{ $existing->created_at->diffForHumans() }}. You can update it below if needed.</p>
        @endif
    </div>
    @endif

    {{-- Declaration form --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <form method="POST"
              action="{{ route('parishioner.my-requirements.store', $serviceRequirement) }}"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Date Completed <span class="text-red-500">*</span></label>
                    <input type="date" name="date_completed" required
                           max="{{ date('Y-m-d') }}"
                           value="{{ old('date_completed', $existing?->date_completed?->format('Y-m-d')) }}"
                           class="form-input w-full @error('date_completed') border-red-400 @enderror">
                    @error('date_completed')<p class="form-error">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Must not be in the future.</p>
                </div>
                <div>
                    <label class="form-label">Venue / Organizer <span class="text-red-500">*</span></label>
                    <input type="text" name="venue_or_organizer" required
                           value="{{ old('venue_or_organizer', $existing?->venue_or_organizer) }}"
                           class="form-input w-full @error('venue_or_organizer') border-red-400 @enderror"
                           placeholder="e.g. Diocese of San Pablo, MHC Parish Hall">
                    @error('venue_or_organizer')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Additional Notes (optional)</label>
                <textarea name="notes" rows="2" class="form-input w-full"
                          placeholder="Any additional context for the parish office…">{{ old('notes', $existing?->notes) }}</textarea>
            </div>

            {{-- Proof upload --}}
            @if($serviceRequirement->requires_proof)
            <div>
                <label class="form-label">
                    Proof of Completion
                    @if($existing?->proof_path)
                    <span class="text-gray-400 font-normal">(leave blank to keep existing)</span>
                    @else
                    <span class="text-red-500">*</span>
                    @endif
                </label>

                @if($existing?->proof_path)
                <div class="mb-3 flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    @if($existing->proofIsImage())
                    <img src="{{ $existing->proofUrl() }}" class="w-16 h-12 object-cover rounded border" alt="Current proof">
                    @else
                    <svg class="w-8 h-8 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    @endif
                    <div>
                        <p class="text-xs font-semibold text-gray-700">Current proof attached</p>
                        <a href="{{ $existing->proofUrl() }}" target="_blank" class="text-xs text-blue-600 hover:underline">View file</a>
                    </div>
                </div>
                @endif

                <input type="file" name="proof"
                       accept=".pdf,.jpg,.jpeg,.png,.gif,.docx"
                       class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100
                              @error('proof') border border-red-400 rounded @enderror">
                <p class="text-xs text-gray-400 mt-1">
                    Accepted: PDF, JPG, PNG, GIF, DOCX — max 10 MB.
                    @if($serviceRequirement->accepts_file_types ?? false)
                    Preferred: {{ $serviceRequirement->acceptedTypesLabel() }}
                    @endif
                </p>
                @error('proof')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            @endif

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="btn-primary">
                    {{ $existing ? 'Update Declaration' : 'Submit Declaration' }}
                </button>
                <a href="{{ route('parishioner.my-requirements.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

</div>
@endsection
