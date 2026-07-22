@extends('layouts.app')

@section('title', 'Certificates')
@section('page-title', 'Certificates')

@section('content')
<div class="py-6 space-y-4">

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" data-live-search data-target="#certificates-table" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-input text-sm w-48" placeholder="Name or cert #…" data-live-input>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Type</label>
                <select name="type" class="form-select text-sm" data-live-input>
                    <option value="">All Types</option>
                    <option value="baptism" @selected(request('type') === 'baptism')>Baptism</option>
                    <option value="confirmation" @selected(request('type') === 'confirmation')>Confirmation</option>
                    <option value="marriage" @selected(request('type') === 'marriage')>Marriage</option>
                    <option value="first_communion" @selected(request('type') === 'first_communion')>First Communion</option>
                    <option value="other" @selected(request('type') === 'other')>Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="form-select text-sm" data-live-input>
                    <option value="">All Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="issued" @selected(request('status') === 'issued')>Issued</option>
                    <option value="released" @selected(request('status') === 'released')>Released</option>
                </select>
            </div>
            <button type="submit" class="btn-secondary text-sm">Filter</button>
            @if(request()->hasAny(['search','type','status']))
                <a href="{{ route('admin.certificates.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
            <div class="ml-auto">
                <a href="{{ route('admin.certificates.create') }}" class="btn-primary text-sm">+ New Certificate</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div id="certificates-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Certificate #</th>
                    <th class="px-4 py-3 font-medium">Parishioner</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">Issued Date</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($certificates as $cert)
                @php
                    $statusColors = ['draft' => 'gray', 'issued' => 'blue', 'released' => 'green'];
                    $sc = $statusColors[$cert->status] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $cert->certificate_number }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('admin.certificates.show', $cert) }}" class="hover:text-blue-700">
                            {{ $cert->parishioner->full_name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600 capitalize">{{ str_replace('_', ' ', $cert->type) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $cert->issued_date->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-800">
                            {{ ucfirst($cert->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="{{ route('admin.certificates.show', $cert) }}" class="action-btn action-btn-view">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                            <a href="{{ route('admin.certificates.download', $cert) }}" class="action-btn action-btn-green">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                PDF
                            </a>
                            @if($cert->status === 'issued')
                            <form method="POST" action="{{ route('admin.certificates.release', $cert) }}" class="inline">
                                @csrf
                                <button type="submit" class="action-btn" style="background:#f5f3ff;color:#6d28d9;border-color:#ddd6fe;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Release
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No certificates found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $certificates->links() }}
        </div>
    </div>
</div>
@endsection
