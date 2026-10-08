@extends('layouts.app')

@section('title', 'Review Requirements — ' . $booking->reference_number)
@section('page-title', 'Review Requirements')

@section('content')
<div class="py-6 max-w-4xl space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.booking-requirements.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Requirements — {{ $booking->reference_number }}</h1>
            <p class="text-sm text-gray-500">{{ $booking->parishioner->full_name }} &mdash; {{ $booking->getTypeLabel() }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    {{-- Progress Bar --}}
    @php
        $progress  = $booking->requirementsProgress();
        $pct       = $progress['total'] > 0 ? round(($progress['approved'] / $progress['total']) * 100) : 0;
        $allDone   = $booking->requirements_approved_at !== null;
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">Requirements Progress</span>
            <span class="text-sm font-bold {{ $allDone ? 'text-green-600' : 'text-blue-600' }}">
                {{ $progress['approved'] }} / {{ $progress['total'] }} Required Approved
            </span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2.5">
            <div class="h-2.5 rounded-full transition-all {{ $allDone ? 'bg-green-500' : 'bg-blue-500' }}"
                 style="width: {{ $pct }}%"></div>
        </div>
        @if($allDone)
        <p class="mt-2 text-xs text-green-600 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            All requirements approved on {{ $booking->requirements_approved_at->format('M d, Y g:ia') }}
        </p>
        @endif

        {{-- Approve All button --}}
        @if(!$allDone && $booking->bookingRequirements->isNotEmpty())
        <form method="POST" action="{{ route('admin.booking-requirements.approve-all', $booking) }}" class="mt-3">
            @csrf
            <button type="submit" class="btn-primary text-sm"
                    onclick="return confirm('Mark ALL requirements as approved for this booking?')">
                ✓ Approve All Requirements
            </button>
        </form>
        @endif
    </div>

    {{-- Requirement Items --}}
    @forelse($booking->bookingRequirements as $item)
    @php
        $req    = $item->requirement;
        $colors = ['pending'=>'yellow','approved'=>'green','needs_revision'=>'red'];
        $color  = $colors[$item->status] ?? 'gray';
        $labels = ['pending'=>'Pending','approved'=>'Approved','needs_revision'=>'Needs Revision'];
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
         id="req-{{ $item->id }}">
        <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-4">
            <div>
                <p class="font-bold text-gray-900">{{ $req->name }}
                    @if($req->is_required)
                    <span class="ml-1.5 text-xs font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded">Required</span>
                    @else
                    <span class="ml-1.5 text-xs font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Optional</span>
                    @endif
                </p>
                @if($req->description)
                <p class="text-xs text-gray-500 mt-0.5">{{ $req->description }}</p>
                @endif
            </div>
            <span class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                bg-{{ $color }}-100 text-{{ $color }}-700 border border-{{ $color }}-200">
                {{ $labels[$item->status] ?? $item->status }}
            </span>
        </div>

        <div class="px-5 py-4 space-y-3">
            {{-- Submission content --}}
            @if($item->submitted_at)
                @if($req->type === 'file' && $item->file_path)
                    @php $ext = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION)); @endphp
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Uploaded File</p>
                        @if(in_array($ext, ['jpg','jpeg','png','gif','webp']))
                        <a href="{{ $item->fileUrl() }}" target="_blank">
                            <img src="{{ $item->fileUrl() }}" alt="Uploaded document"
                                 class="max-h-56 rounded-lg border border-gray-200 object-contain hover:opacity-90 transition">
                        </a>
                        @else
                        <a href="{{ $item->fileUrl() }}" target="_blank"
                           class="inline-flex items-center gap-2 bg-blue-50 text-blue-700 border border-blue-200 px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            View {{ strtoupper($ext) }} Document
                        </a>
                        @endif
                    </div>
                @elseif($req->type === 'checkbox')
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                        <svg class="w-5 h-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Parishioner confirmed: <em>"{{ $req->name }}"</em>
                    </div>
                @elseif($req->type === 'text' && $item->text_value)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Submitted Text</p>
                        <p class="text-sm text-gray-800 bg-gray-50 rounded-lg px-3 py-2 border border-gray-100">{{ $item->text_value }}</p>
                    </div>
                @endif

                @if($item->parishioner_note)
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Parishioner Note</p>
                    <p class="text-sm text-gray-700 italic">{{ $item->parishioner_note }}</p>
                </div>
                @endif

                <p class="text-xs text-gray-400">Submitted {{ $item->submitted_at->diffForHumans() }}</p>

            @else
                <p class="text-sm text-gray-400 italic">Not yet submitted by parishioner.</p>
            @endif

            {{-- Previous admin remark --}}
            @if($item->admin_remark)
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-xs text-amber-800">
                <span class="font-bold">Previous remark:</span> {{ $item->admin_remark }}
                @if($item->reviewedBy) &mdash; {{ $item->reviewedBy->name }} @endif
            </div>
            @endif

            {{-- Action buttons --}}
            @if($item->submitted_at && $item->status !== 'approved')
            <div class="flex flex-wrap gap-2 mt-2">
                {{-- Approve --}}
                <form method="POST" action="{{ route('admin.booking-requirements.approve', $item) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Approve
                    </button>
                </form>

                {{-- Request Revision --}}
                <button type="button" onclick="toggleRevisionForm({{ $item->id }})"
                        class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Request Revision
                </button>
            </div>

            {{-- Revision remark form --}}
            <form method="POST" action="{{ route('admin.booking-requirements.request-revision', $item) }}"
                  id="revision-form-{{ $item->id }}" class="hidden mt-2 space-y-2">
                @csrf
                <textarea name="admin_remark" rows="2" required
                          class="form-input w-full text-sm"
                          placeholder="Explain what needs to be corrected or resubmitted…"></textarea>
                <div class="flex gap-2">
                    <button type="submit" class="btn-danger text-sm">Send Revision Request</button>
                    <button type="button" onclick="toggleRevisionForm({{ $item->id }})" class="btn-secondary text-sm">Cancel</button>
                </div>
            </form>

            @elseif($item->status === 'approved')
            <p class="text-xs text-green-600 font-semibold flex items-center gap-1 mt-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Approved
                @if($item->reviewedBy) by {{ $item->reviewedBy->name }}@endif
                @if($item->reviewed_at) on {{ $item->reviewed_at->format('M d, Y') }}@endif
            </p>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
        <p>No requirements have been submitted for this booking yet.</p>
    </div>
    @endforelse

</div>

@push('scripts')
<script>
function toggleRevisionForm(id) {
    const form = document.getElementById('revision-form-' + id);
    form.classList.toggle('hidden');
}
</script>
@endpush
@endsection
