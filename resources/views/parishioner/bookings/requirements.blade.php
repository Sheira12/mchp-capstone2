@extends('layouts.portal')

@section('title', 'Submit Requirements — ' . $service->name)

@push('styles')
<style>
.req-card { background:#fff; border:1.5px solid #e2e8f0; border-radius:1rem; overflow:hidden; transition:border-color 0.15s; }
.req-card.approved { border-color:#86efac; background:#f0fdf4; }
.req-card.needs_revision { border-color:#fca5a5; background:#fff5f5; }
.req-card.pending { border-color:#fde047; }
.req-header { padding:1rem 1.25rem 0.75rem; border-bottom:1px solid #f1f5f9; display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; }
.req-body { padding:1rem 1.25rem; }
.badge { display:inline-flex; align-items:center; gap:4px; font-size:0.7rem; font-weight:700; padding:2px 10px; border-radius:9999px; white-space:nowrap; }
.badge-pending  { background:#fef9c3; color:#854d0e; border:1px solid #fde047; }
.badge-approved { background:#dcfce7; color:#166534; border:1px solid #86efac; }
.badge-revision { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
.badge-required { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; font-size:0.65rem; }
.badge-optional { background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; font-size:0.65rem; }
.progress-bar-wrap { background:#e2e8f0; border-radius:9999px; height:8px; overflow:hidden; }
.progress-bar-fill { height:8px; border-radius:9999px; background:linear-gradient(to right,#3b82f6,#6366f1); transition:width 0.4s; }
.lock-overlay { background:linear-gradient(135deg,#f8fafc,#eff6ff); border:1.5px solid #bfdbfe; border-radius:1rem; padding:2rem; text-align:center; }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-3xl w-full">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.bookings.create') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Requirements Submission</h1>
            <p class="text-sm text-gray-500">Service: <strong>{{ $service->name }}</strong></p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
    @endif

    {{-- Step progress indicator --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-4">
        <div class="flex items-center gap-0">
            @php $steps = [['num'=>1,'label'=>'Submit Requirements','active'=>true],['num'=>2,'label'=>'Pick a Date & Time','active'=>false],['num'=>3,'label'=>'Confirm Booking','active'=>false]]; @endphp
            @foreach($steps as $i => $step)
            <div class="flex items-center {{ $i < count($steps)-1 ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                        {{ $step['active'] ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                        {{ $step['num'] }}
                    </div>
                    <p class="text-xs mt-1 font-semibold {{ $step['active'] ? 'text-blue-600' : 'text-gray-400' }} text-center w-24">{{ $step['label'] }}</p>
                </div>
                @if($i < count($steps)-1)
                <div class="flex-1 h-px bg-gray-200 mx-2 mb-4"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Progress summary --}}
    @php
        $totalReq   = $requirements->where('is_required', true)->count();
        $approved   = $bookingRequirements->where('status', 'approved')->whereIn('service_requirement_id', $requirements->where('is_required', true)->pluck('id'))->count();
        $pct        = $totalReq > 0 ? round(($approved / $totalReq) * 100) : 0;
        $allApproved = $booking && $booking->requirements_approved_at;
    @endphp

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">Required Requirements Approved</span>
            <span class="text-sm font-bold {{ $allApproved ? 'text-green-600' : 'text-blue-600' }}">{{ $approved }} / {{ $totalReq }}</span>
        </div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill {{ $allApproved ? '!bg-green-500' : '' }}" style="width:{{ $pct }}%"></div>
        </div>
        @if($allApproved)
        <p class="mt-2 text-sm text-green-700 font-semibold flex items-center gap-1.5">
            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            All requirements approved! You can now schedule your booking.
        </p>
        @elseif($approved > 0)
        <p class="mt-2 text-xs text-gray-500">Waiting for admin to review your submissions. You will be notified by email.</p>
        @else
        <p class="mt-2 text-xs text-gray-500">Please submit all required documents below. The parish office will review them.</p>
        @endif
    </div>

    {{-- Requirement items --}}
    @foreach($requirements as $req)
    @php
        $submitted = $bookingRequirements->firstWhere('service_requirement_id', $req->id);
        $status    = $submitted?->status ?? 'not_submitted';
    @endphp

    <div class="req-card {{ $submitted ? $status : '' }}">
        <div class="req-header">
            <div class="flex-1">
                <p class="font-bold text-gray-900 text-sm">{{ $req->name }}</p>
                @if($req->description)
                <p class="text-xs text-gray-500 mt-0.5">{{ $req->description }}</p>
                @endif
                @if($req->type === 'file' && $req->accepted_file_types)
                <p class="text-xs text-blue-500 mt-0.5">Accepts: {{ $req->acceptedTypesLabel() }}</p>
                @endif
            </div>
            <div class="flex flex-col items-end gap-1 shrink-0">
                <span class="badge {{ $req->is_required ? 'badge-required' : 'badge-optional' }}">
                    {{ $req->is_required ? 'Required' : 'Optional' }}
                </span>
                @if($submitted)
                    @if($status === 'approved')
                    <span class="badge badge-approved">✓ Approved</span>
                    @elseif($status === 'needs_revision')
                    <span class="badge badge-revision">⚠ Needs Revision</span>
                    @else
                    <span class="badge badge-pending">⏳ Pending Review</span>
                    @endif
                @else
                <span class="badge" style="background:#f1f5f9;color:#94a3b8;border:1px solid #e2e8f0;">Not Submitted</span>
                @endif
            </div>
        </div>

        <div class="req-body">
            {{-- Admin remark --}}
            @if($submitted?->admin_remark)
            <div class="mb-3 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-xs text-amber-800">
                <span class="font-bold">Admin note:</span> {{ $submitted->admin_remark }}
            </div>
            @endif

            {{-- If approved, show what was submitted --}}
            @if($status === 'approved')
            <p class="text-sm text-green-700 font-medium flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                This requirement has been approved.
            </p>

            {{-- Pending — show what was sent but don't allow re-upload --}}
            @elseif($status === 'pending')
            <p class="text-sm text-yellow-700">Your submission is under review. You will be notified once it is approved.</p>
            @if($submitted->file_path)
            <p class="text-xs text-gray-500 mt-1">File submitted: {{ $submitted->fileName() }}</p>
            @endif

            {{-- Not submitted OR needs revision — show upload form --}}
            @else
            <form method="POST" action="{{ route('parishioner.bookings.requirements.upload', [$booking, $req]) }}"
                  enctype="multipart/form-data" class="space-y-3">
                @csrf

                @if($req->type === 'file')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Upload Document</label>
                    <input type="file" name="file" required accept="{{ $req->acceptAttribute() }}"
                           class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                @elseif($req->type === 'checkbox')
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" name="text_value" value="confirmed" required
                           class="mt-0.5 rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700">I confirm: <em>{{ $req->name }}</em></span>
                </label>

                @elseif($req->type === 'text')
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Your Answer</label>
                    <textarea name="text_value" rows="2" required class="form-input w-full text-sm"
                              placeholder="{{ $req->description ?? 'Enter your answer…' }}">{{ old('text_value_' . $req->id) }}</textarea>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Note (optional)</label>
                    <input type="text" name="parishioner_note" class="form-input w-full text-sm"
                           placeholder="Any additional information for the parish office…">
                </div>

                <button type="submit" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    {{ $status === 'needs_revision' ? 'Resubmit' : 'Submit' }}
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach

    {{-- Proceed to booking --}}
    <div class="{{ $allApproved ? 'bg-green-50 border-green-200' : 'lock-overlay border-gray-200' }} border rounded-2xl p-6 text-center">
        @if($allApproved)
        <svg class="w-12 h-12 mx-auto text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
        <h3 class="font-bold text-gray-900 text-lg mb-1">Requirements Approved!</h3>
        <p class="text-sm text-gray-600 mb-4">You can now proceed to select a date and complete your booking.</p>
        <a href="{{ route('parishioner.bookings.create', ['service' => $service->slug, 'booking_id' => $booking->id]) }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-xl transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Proceed to Schedule Booking
        </a>
        @else
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zM10 11V7a2 2 0 114 0v4"/></svg>
        <h3 class="font-bold text-gray-700 text-lg mb-1">Booking Locked</h3>
        <p class="text-sm text-gray-500">Complete and submit all required items above, then wait for parish office approval before scheduling your booking.</p>
        <p class="text-xs text-gray-400 mt-2">{{ $approved }} of {{ $totalReq }} required items approved.</p>
        @endif
    </div>

</div>
@endsection
