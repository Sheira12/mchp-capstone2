@extends('layouts.portal')
@section('title', 'My Requirements')

@push('styles')
<style>
.req-row { display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid #f1f5f9; }
.req-row:last-child { border-bottom:none; }
.req-icon { width:20px; height:20px; flex-shrink:0; margin-top:2px; }
.state-badge { display:inline-flex; align-items:center; gap:4px; font-size:.7rem; font-weight:700; padding:2px 10px; border-radius:9999px; }
.state-satisfied { background:#dcfce7; color:#166534; border:1px solid #86efac; }
.state-pending   { background:#fef9c3; color:#854d0e; border:1px solid #fde047; }
.state-rejected  { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
.state-missing   { background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-3xl w-full">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Requirements</h1>
            <p class="text-sm text-gray-500 mt-0.5">Submit and track the documents and seminars required to book parish services.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('info'))
    <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('info') }}</div>
    @endif

    {{-- New-account friendly prompt --}}
    @if($isNew)
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl px-6 py-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-2xl shrink-0">👋</div>
            <div>
                <h3 class="font-bold text-blue-900 text-lg mb-1">New here? Let us know what you've already completed.</h3>
                <p class="text-sm text-blue-700 leading-relaxed">
                    If you've attended seminars or have certificates from another parish, you can declare them below.
                    Once verified by our office, you'll be eligible to book the relevant services.
                    Parishioners already in our records are automatically matched.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Per-service requirement cards --}}
    @forelse($services as $service)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-gray-900">{{ $service->name }}</h2>
                <p class="text-xs text-gray-500 mt-0.5">Required before booking this service</p>
            </div>
            @php
                $allSatisfied = $service->serviceRequirements->every(function($req) use ($declarations) {
                    $d = $declarations->get($req->id);
                    return $d && $d->status === 'verified';
                });
            @endphp
            @if($allSatisfied)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Eligible
            </span>
            @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                Not Yet Eligible
            </span>
            @endif
        </div>

        <div class="px-5 py-4 divide-y divide-gray-50">
            @foreach($service->serviceRequirements as $req)
            @php
                $decl = $declarations->get($req->id);
                $state = match(true) {
                    $decl && $decl->status === 'verified'  => 'satisfied',
                    $decl && $decl->status === 'pending'   => 'pending',
                    $decl && $decl->status === 'rejected'  => 'rejected',
                    default                                => 'missing',
                };
                $canDeclare = !$decl || ($decl->status !== 'verified');
                $typeIcon = match($req->eligibility_type ?? $req->type) {
                    'seminar'          => '🎓',
                    'sacrament_record' => '✝',
                    default            => '📄',
                };
            @endphp
            <div class="req-row">
                {{-- State icon --}}
                @if($state === 'satisfied')
                <svg class="req-icon text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                @elseif($state === 'pending')
                <svg class="req-icon text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                @elseif($state === 'rejected')
                <svg class="req-icon text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                @else
                <svg class="req-icon text-gray-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/></svg>
                @endif

                <div class="flex-1">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div>
                            <span class="text-base mr-1">{{ $typeIcon }}</span>
                            <span class="font-semibold text-gray-900 text-sm">{{ $req->name }}</span>
                            @if(!$req->is_required)
                            <span class="ml-1 text-xs text-gray-400">(optional)</span>
                            @endif
                        </div>
                        <span class="state-badge state-{{ $state }}">
                            {{ ['satisfied'=>'✓ Verified','pending'=>'⏳ Pending','rejected'=>'✗ Rejected','missing'=>'○ Not Submitted'][$state] }}
                        </span>
                    </div>

                    @if($req->description)
                    <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">{{ $req->description }}</p>
                    @endif

                    @if($decl && $state === 'pending')
                    <p class="text-xs text-yellow-700 mt-1">Submitted {{ $decl->created_at->diffForHumans() }} — awaiting parish office review.</p>
                    @elseif($decl && $state === 'rejected')
                    <p class="text-xs text-red-600 mt-1 font-medium">Reason: {{ $decl->rejection_reason }}</p>
                    @elseif($decl && $state === 'satisfied')
                    <p class="text-xs text-green-600 mt-1">
                        Verified{{ $decl->date_completed ? ' — completed ' . $decl->date_completed->format('M d, Y') : '' }}
                        {{ $decl->venue_or_organizer ? 'at ' . $decl->venue_or_organizer : '' }}
                    </p>
                    @endif

                    @if($canDeclare && $req->eligibility_type !== 'sacrament_record')
                    <a href="{{ route('parishioner.my-requirements.declare', $req) }}"
                       class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        {{ $state === 'rejected' ? 'Resubmit' : ($state === 'pending' ? 'View / Update' : 'Declare Completion') }}
                    </a>
                    @elseif($canDeclare && $req->eligibility_type === 'sacrament_record')
                    <a href="{{ route('parishioner.my-requirements.declare', $req) }}"
                       class="inline-flex items-center gap-1 mt-2 text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Declare (from another parish)
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="font-medium">No service requirements are configured yet.</p>
        <p class="text-sm mt-1">All services are currently open — you can book freely.</p>
        <a href="{{ route('parishioner.bookings.create') }}" class="mt-4 inline-block btn-primary text-sm">Book a Service</a>
    </div>
    @endforelse

</div>
@endsection
