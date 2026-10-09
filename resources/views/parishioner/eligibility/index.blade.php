@extends('layouts.portal')
@section('title', 'My Eligibility')

@push('styles')
<style>
.elig-card { background:#fff; border-radius:1rem; border:1.5px solid #e2e8f0; overflow:hidden; }
.elig-card.all-ok  { border-color:#86efac; }
.elig-card.not-ok  { border-color:#fca5a5; }
.rule-row { display:flex; align-items:flex-start; gap:10px; padding:8px 0; border-bottom:1px solid #f8fafc; }
.rule-row:last-child { border-bottom:none; }
.rule-icon { width:18px; height:18px; flex-shrink:0; margin-top:1px; }
.rule-name  { font-size:.85rem; font-weight:600; color:#0f172a; }
.rule-msg   { font-size:.78rem; color:#64748b; margin-top:1px; line-height:1.4; }
.rule-action { display:inline-flex; align-items:center; gap:4px; margin-top:4px; font-size:.72rem; font-weight:700; color:#2563eb; text-decoration:underline; cursor:pointer; }
.prog-bar   { height:6px; border-radius:9999px; background:#e2e8f0; overflow:hidden; margin-top:6px; }
.prog-fill  { height:6px; border-radius:9999px; background:linear-gradient(to right,#3b82f6,#6366f1); transition:width .4s; }
.prog-fill.complete { background:#22c55e; }
</style>
@endpush

@section('content')
<div class="space-y-5 max-w-3xl w-full">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Eligibility</h1>
        <p class="text-sm text-gray-500 mt-0.5">Check what you need to complete before booking each service.</p>
    </div>

    @if($results->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="font-medium">No eligibility rules configured yet.</p>
        <p class="text-sm mt-1">All services are currently open — you can book freely.</p>
        <a href="{{ route('parishioner.bookings.create') }}" class="mt-4 inline-block btn-primary text-sm">Book a Service</a>
    </div>
    @endif

    @foreach($results as $entry)
    @php
        $svc    = $entry['service'];
        $result = $entry['result'];
        $prog   = $result->progress();
        $ok     = $result->isEligible();
    @endphp

    <div class="elig-card {{ $ok ? 'all-ok' : 'not-ok' }}">
        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 px-5 py-4
                    {{ $ok ? 'bg-green-50' : 'bg-red-50' }}
                    border-b {{ $ok ? 'border-green-100' : 'border-red-100' }}">
            <div class="flex-1">
                <p class="font-bold text-gray-900">{{ $svc->name }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <div class="prog-bar flex-1">
                        <div class="prog-fill {{ $ok ? 'complete' : '' }}" style="width:{{ $prog['pct'] }}%"></div>
                    </div>
                    <span class="text-xs font-semibold {{ $ok ? 'text-green-700' : 'text-gray-600' }} shrink-0">
                        {{ $prog['satisfied'] }}/{{ $prog['total'] }} required
                    </span>
                </div>
            </div>
            <div>
                @if($ok)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Eligible to Book
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    Not Yet Eligible
                </span>
                @endif
            </div>
        </div>

        {{-- Criteria list --}}
        <div class="px-5 py-4 divide-y divide-gray-50">
            @foreach($result->items() as $item)
            <div class="rule-row">
                {{-- Status icon --}}
                @if($item->satisfied)
                <svg class="rule-icon text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                @elseif($item->status === 'pending')
                <svg class="rule-icon text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                @elseif($item->status === 'waived')
                <svg class="rule-icon text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/></svg>
                @else
                <svg class="rule-icon text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                @endif

                <div class="flex-1">
                    <p class="rule-name">
                        {{ $item->ruleName }}
                        @if(!$item->required)
                        <span class="ml-1 text-xs font-normal text-gray-400">(optional)</span>
                        @endif
                        @if($item->isPlaceholder)
                        <span class="ml-1 text-xs text-amber-500 font-bold" title="Placeholder — confirm with parish office">⚠</span>
                        @endif
                    </p>
                    <p class="rule-msg">{{ $item->message }}</p>

                    @if($item->actionUrl && !$item->satisfied)
                    <a href="{{ $item->actionUrl }}" class="rule-action">
                        {{ $item->actionLabel ?? 'Take Action' }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    @endif
                </div>

                {{-- Status badge --}}
                @php $bc = ['satisfied'=>'green','pending'=>'yellow','waived'=>'blue','missing'=>'red'][$item->status] ?? 'gray'; @endphp
                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-bold
                      bg-{{ $bc }}-100 text-{{ $bc }}-700 border border-{{ $bc }}-200">
                    {{ $item->statusLabel() }}
                </span>
            </div>
            @endforeach
        </div>

        {{-- Footer CTA --}}
        <div class="px-5 pb-4">
            @if($ok)
            <a href="{{ route('parishioner.bookings.create', ['preselect' => $svc->slug]) }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Book {{ $svc->name }}
            </a>
            @else
            <p class="text-xs text-gray-400">Complete the requirements above to unlock booking for {{ $svc->name }}.</p>
            @endif
        </div>
    </div>
    @endforeach

</div>
@endsection
