@extends('layouts.app')

@section('title', 'Sacramental Records')
@section('page-title', 'Sacramental Records')

@section('content')
<div class="py-6 space-y-4">

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" data-live-search data-target="#records-table" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-input text-sm w-48" placeholder="Parishioner name…" data-live-input>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Type</label>
                <select name="type" class="form-select text-sm" data-live-input>
                    <option value="">All Types</option>
                    <option value="baptism" @selected(request('type') === 'baptism')>Baptism</option>
                    <option value="first_communion" @selected(request('type') === 'first_communion')>First Communion</option>
                    <option value="confirmation" @selected(request('type') === 'confirmation')>Confirmation</option>
                    <option value="marriage" @selected(request('type') === 'marriage')>Marriage</option>
                    <option value="death_burial" @selected(request('type') === 'death_burial')>Death/Burial</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm" data-live-input>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm" data-live-input>
            </div>
            <button type="submit" class="btn-secondary text-sm">Filter</button>
            @if(request()->hasAny(['search','type','date_from','date_to']))
                <a href="{{ route('admin.sacramental-records.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
            <div class="ml-auto">
                <a href="{{ route('admin.sacramental-records.create') }}" class="btn-primary text-sm">+ New Record</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div id="records-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Parishioner</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Celebrant</th>
                    <th class="px-4 py-3 font-medium">Register #</th>
                    <th class="px-4 py-3 font-medium">Verified</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($records as $record)
                @php
                    $typeColors = [
                        'baptism' => 'blue', 'first_communion' => 'green',
                        'confirmation' => 'purple', 'marriage' => 'pink', 'death_burial' => 'gray'
                    ];
                    $typeLabels = [
                        'baptism' => 'Baptism', 'first_communion' => 'First Communion',
                        'confirmation' => 'Confirmation', 'marriage' => 'Marriage', 'death_burial' => 'Death/Burial'
                    ];
                    $color = $typeColors[$record->type] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('admin.sacramental-records.show', $record) }}" class="hover:text-blue-700">
                            {{ $record->parishioner->full_name }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ $typeLabels[$record->type] ?? $record->type }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $record->date_administered->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $record->celebrant }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $record->register_number ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($record->verified_at)
                            <span class="text-green-600 text-xs">✓ Verified</span>
                        @else
                            <span class="text-gray-400 text-xs">Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="{{ route('admin.sacramental-records.show', $record) }}" class="action-btn action-btn-view">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                            <a href="{{ route('admin.sacramental-records.edit', $record) }}" class="action-btn action-btn-edit">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">No records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
