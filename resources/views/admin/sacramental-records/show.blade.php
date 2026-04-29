@extends('layouts.app')

@section('title', 'Sacramental Record')
@section('page-title', 'Sacramental Record')

@section('content')
@php
    $typeLabels = [
        'baptism' => 'Baptism', 'first_communion' => 'First Communion',
        'confirmation' => 'Confirmation', 'marriage' => 'Marriage', 'death_burial' => 'Death/Burial'
    ];
    $typeColors = [
        'baptism' => 'blue', 'first_communion' => 'green',
        'confirmation' => 'purple', 'marriage' => 'pink', 'death_burial' => 'gray'
    ];
    $color = $typeColors[$sacramentalRecord->type] ?? 'gray';
@endphp
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.sacramental-records.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Records</a>
        <div class="ml-auto flex gap-2">
            @if(!$sacramentalRecord->verified_at)
            <form method="POST" action="{{ route('admin.sacramental-records.verify', $sacramentalRecord) }}">
                @csrf
                <button type="submit" class="btn-secondary text-sm">✓ Verify</button>
            </form>
            @endif
            <a href="{{ route('admin.sacramental-records.edit', $sacramentalRecord) }}" class="btn-secondary text-sm">Edit</a>
            <form method="POST" action="{{ route('admin.sacramental-records.destroy', $sacramentalRecord) }}"
                  onsubmit="return confirm('Delete this record?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger text-sm">Delete</button>
            </form>
        </div>
    </div>

    {{-- Record Details --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $color }}-100 text-{{ $color }}-800 mb-2">
                    {{ $typeLabels[$sacramentalRecord->type] ?? $sacramentalRecord->type }}
                </span>
                <h2 class="text-xl font-bold text-gray-900">{{ $sacramentalRecord->parishioner->full_name }}</h2>
                @if($sacramentalRecord->spouseParishioner)
                    <p class="text-gray-500 text-sm">Spouse: {{ $sacramentalRecord->spouseParishioner->full_name }}</p>
                @endif
            </div>
            @if($sacramentalRecord->verified_at)
                <div class="text-right">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        ✓ Verified
                    </span>
                    <p class="text-xs text-gray-400 mt-1">by {{ $sacramentalRecord->verifiedBy?->name }}</p>
                    <p class="text-xs text-gray-400">{{ $sacramentalRecord->verified_at->format('M d, Y') }}</p>
                </div>
            @endif
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Date Administered</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $sacramentalRecord->date_administered->format('F d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Celebrant</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $sacramentalRecord->celebrant }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Venue</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $sacramentalRecord->venue ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Register Number</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $sacramentalRecord->register_number ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Page / Line</dt>
                <dd class="font-medium text-gray-900 mt-0.5">
                    {{ $sacramentalRecord->page_number ?? '—' }} / {{ $sacramentalRecord->line_number ?? '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Recorded By</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $sacramentalRecord->recordedBy?->name ?? '—' }}</dd>
            </div>
        </dl>

        @if($sacramentalRecord->godparents)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <dt class="text-sm text-gray-500 mb-1">Godparents / Sponsors</dt>
            <dd class="flex flex-wrap gap-2">
                @foreach($sacramentalRecord->godparents as $gp)
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-sm">{{ $gp }}</span>
                @endforeach
            </dd>
        </div>
        @endif

        @if($sacramentalRecord->witnesses)
        <div class="mt-3">
            <dt class="text-sm text-gray-500 mb-1">Witnesses</dt>
            <dd class="flex flex-wrap gap-2">
                @foreach($sacramentalRecord->witnesses as $w)
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-sm">{{ $w }}</span>
                @endforeach
            </dd>
        </div>
        @endif

        @if($sacramentalRecord->notes)
        <div class="mt-3 pt-3 border-t border-gray-100">
            <dt class="text-sm text-gray-500 mb-1">Notes</dt>
            <dd class="text-sm text-gray-700">{{ $sacramentalRecord->notes }}</dd>
        </div>
        @endif
    </div>

    {{-- Linked Certificate --}}
    @if($sacramentalRecord->certificate)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-3">Linked Certificate</h3>
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-900">{{ $sacramentalRecord->certificate->certificate_number }}</p>
                <p class="text-sm text-gray-500">Status: {{ ucfirst($sacramentalRecord->certificate->status) }}</p>
            </div>
            <a href="{{ route('admin.certificates.show', $sacramentalRecord->certificate) }}" class="btn-secondary text-sm">View Certificate</a>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">No certificate generated yet.</p>
            <a href="{{ route('admin.certificates.create') }}?sacramental_record_id={{ $sacramentalRecord->id }}&parishioner_id={{ $sacramentalRecord->parishioner_id }}"
               class="btn-primary text-sm">Generate Certificate</a>
        </div>
    </div>
    @endif
</div>
@endsection
