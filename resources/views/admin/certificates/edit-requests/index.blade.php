@extends('layouts.app')
@section('title', 'Certificate Correction Requests')
@section('page-title', 'Certificate Correction Requests')

@section('content')
<div class="py-6 space-y-4">

    @if($pendingCount > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 bg-amber-400 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        </div>
        <span class="font-bold text-amber-800 text-sm">{{ $pendingCount }} correction request{{ $pendingCount !== 1 ? 's' : '' }} awaiting review</span>
    </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <div class="relative">
                    <select name="status" class="form-select text-sm pr-8 appearance-none" onchange="this.form.submit()">
                        <option value="pending"  @selected(request('status','pending') === 'pending')>⏳ Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>✓ Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>✗ Rejected</option>
                        <option value="all"      @selected(request('status') === 'all')>All</option>
                    </select>
                    <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </div>
            <a href="{{ route('admin.certificates.index') }}" class="btn-secondary text-sm self-end">← Certificates</a>
        </form>
    </div>

    @forelse($editRequests as $req)
    @php
        $cert   = $req->certificate;
        $badgeClass = match($req->status) {
            'pending'  => 'bg-amber-100 text-amber-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default    => 'bg-gray-100 text-gray-600',
        };
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-3 flex-wrap">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <a href="{{ route('admin.certificates.show', $cert) }}" class="font-bold text-gray-900 hover:text-blue-700 text-sm">
                        {{ $cert->getTypeLabel() }}
                    </a>
                    <span class="font-mono text-xs text-gray-400">{{ $cert->certificate_number }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">
                        {{ ucfirst($req->status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-600">
                    <span class="font-medium">{{ $cert->parishioner->full_name }}</span>
                    · Submitted by {{ $req->submittedBy->name }}
                    · {{ $req->created_at->format('M d, Y g:i A') }}
                </p>
            </div>
        </div>

        {{-- Requested changes --}}
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Requested Changes</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($req->requested_changes as $field => $value)
                <div class="flex gap-2 text-sm">
                    <span class="text-gray-500 min-w-[130px] flex-shrink-0">{{ \App\Models\CertificateEditRequest::fieldLabel($field) }}:</span>
                    <span class="font-medium text-gray-900">
                        @if(is_array($value))
                            {{ implode(', ', array_filter($value)) }}
                        @else
                            {{ $value }}
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
            @if($req->parishioner_note)
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500 mb-1">Parishioner note:</p>
                <p class="text-sm text-gray-700 italic">"{{ $req->parishioner_note }}"</p>
            </div>
            @endif
        </div>

        {{-- Staff response (if reviewed) --}}
        @if($req->status !== 'pending')
        <div class="text-sm text-gray-500">
            {{ ucfirst($req->status) }} by <span class="font-medium text-gray-700">{{ $req->reviewedBy?->name }}</span>
            on {{ $req->reviewed_at?->format('M d, Y g:i A') }}
            @if($req->staff_response)
            · <span class="italic">"{{ $req->staff_response }}"</span>
            @endif
        </div>
        @endif

        {{-- Actions --}}
        @if($req->status === 'pending')
        <div class="flex flex-wrap gap-2 pt-1">
            {{-- Approve --}}
            <form method="POST" action="{{ route('admin.certificate-edit-requests.approve', $req) }}" class="flex gap-2 items-end">
                @csrf
                <input type="text" name="staff_response" placeholder="Staff note (optional)"
                       class="form-input text-sm w-64">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg transition"
                        onclick="return confirm('Approve this correction and regenerate the certificate PDF?')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Approve & Regenerate
                </button>
            </form>
            {{-- Reject --}}
            <form method="POST" action="{{ route('admin.certificate-edit-requests.reject', $req) }}" class="flex gap-2 items-end">
                @csrf
                <input type="text" name="staff_response" placeholder="Reason for rejection (required)"
                       class="form-input text-sm w-64" required>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-lg transition"
                        onclick="return confirm('Reject this correction request?')">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Reject
                </button>
            </form>
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-400">
        <div class="text-4xl mb-2">✓</div>
        <p class="font-medium text-gray-500">No correction requests to review</p>
    </div>
    @endforelse

    @if($editRequests->hasPages())
    <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">{{ $editRequests->links() }}</div>
    @endif

</div>
@endsection
