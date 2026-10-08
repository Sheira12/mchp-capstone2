@extends('layouts.app')

@section('title', 'Requirements Review Queue')
@section('page-title', 'Requirements Review Queue')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header + stats --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Requirements Review Queue</h1>
            <p class="text-sm text-gray-500 mt-0.5">Review and approve parishioner-submitted booking requirements.</p>
        </div>
        @if($pendingCount > 0)
        <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-800 text-sm font-bold px-3 py-1.5 rounded-full border border-yellow-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $pendingCount }} Pending
        </span>
        @endif
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-full">
                    <option value="">Pending + Needs Revision</option>
                    <option value="pending"        @selected(request('status')==='pending')>Pending</option>
                    <option value="approved"       @selected(request('status')==='approved')>Approved</option>
                    <option value="needs_revision" @selected(request('status')==='needs_revision')>Needs Revision</option>
                </select>
            </div>
            <div>
                <label class="form-label">Service</label>
                <select name="service" class="form-select w-full">
                    <option value="">All Services</option>
                    @foreach($services as $svc)
                    <option value="{{ $svc->slug }}" @selected(request('service')===$svc->slug)>{{ $svc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Submitted From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input w-full">
            </div>
            <div>
                <label class="form-label">Submitted To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input w-full">
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="btn-primary text-sm">Apply Filters</button>
            <a href="{{ route('admin.booking-requirements.index') }}" class="btn-secondary text-sm">Clear</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        @if($submissions->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="font-medium">No submissions match your filters.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Parishioner</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Service</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Requirement</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Type</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Submitted</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($submissions as $sub)
                    @php
                        $colors = ['pending'=>'yellow','approved'=>'green','needs_revision'=>'red'];
                        $color  = $colors[$sub->status] ?? 'gray';
                        $labels = ['pending'=>'Pending','approved'=>'Approved','needs_revision'=>'Needs Revision'];
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $sub->booking->parishioner->full_name ?? '—' }}
                            <br><span class="text-xs text-gray-400">{{ $sub->booking->reference_number }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $sub->requirement->service->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 max-w-xs">
                            <span class="font-medium">{{ $sub->requirement->name }}</span>
                            @if($sub->requirement->is_required)
                            <span class="ml-1 text-xs text-red-500 font-bold">Required</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 capitalize">{{ $sub->requirement->type ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                bg-{{ $color }}-100 text-{{ $color }}-700 border border-{{ $color }}-200">
                                {{ $labels[$sub->status] ?? $sub->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $sub->submitted_at ? $sub->submitted_at->format('M d, Y g:ia') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.booking-requirements.show', $sub->booking) }}"
                               class="text-blue-600 hover:underline text-sm font-medium">Review</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $submissions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
