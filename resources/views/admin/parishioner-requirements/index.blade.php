@extends('layouts.app')
@section('title', 'Requirement Verifications')
@section('page-title', 'Requirement Verifications')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Requirement Verifications</h1>
            <p class="text-sm text-gray-500 mt-0.5">Review and verify parishioner-declared seminars, documents, and sacramental records.</p>
        </div>
        @if($pendingCount > 0)
        <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-800 text-sm font-bold px-3 py-1.5 rounded-full border border-yellow-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $pendingCount }} Pending
        </span>
        @endif
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select w-full">
                    <option value="pending"  @selected(request('status','pending')==='pending')>Pending</option>
                    <option value="verified" @selected(request('status')==='verified')>Verified</option>
                    <option value="rejected" @selected(request('status')==='rejected')>Rejected</option>
                    <option value="all"      @selected(request('status')==='all')>All</option>
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
                <label class="form-label">Source</label>
                <select name="source" class="form-select w-full">
                    <option value="">All Sources</option>
                    <option value="self_declared"  @selected(request('source')==='self_declared')>Self-Declared</option>
                    <option value="staff_recorded" @selected(request('source')==='staff_recorded')>Staff Recorded</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <a href="{{ route('admin.parishioner-requirements.index') }}" class="btn-secondary text-sm">Clear</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        @if($submissions->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="font-medium">No submissions match your filters.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Parishioner</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Service / Requirement</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Date Completed</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Venue</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Source</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($submissions as $sub)
                    @php
                        $sc = ['pending'=>'yellow','verified'=>'green','rejected'=>'red'][$sub->status] ?? 'gray';
                        $labels = ['pending'=>'Pending','verified'=>'Verified','rejected'=>'Rejected'];
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $sub->parishioner?->full_name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $sub->parishioner?->contact_number }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $sub->requirement?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $sub->requirement?->service?->name }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700 text-xs whitespace-nowrap">
                            {{ $sub->date_completed?->format('M d, Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-700 text-xs">{{ $sub->venue_or_organizer ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                {{ \App\Models\ParishionerRequirement::SOURCES[$sub->source] ?? $sub->source }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $sc }}-100 text-{{ $sc }}-700 border border-{{ $sc }}-200">
                                {{ $labels[$sub->status] ?? $sub->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.parishioner-requirements.show', $sub) }}"
                               class="text-blue-600 hover:underline text-sm font-medium">Review</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $submissions->links() }}</div>
        @endif
    </div>
</div>
@endsection
