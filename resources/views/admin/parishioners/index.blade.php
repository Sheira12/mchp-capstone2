@extends('layouts.app')

@section('title', 'Parishioners')
@section('page-title', 'Parishioners')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500">{{ $parishioners->total() }} parishioners found</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.families.create') }}" class="btn-secondary text-sm">+ New Family</a>
            <a href="{{ route('admin.parishioners.create') }}" class="btn-primary text-sm">+ Add Parishioner</a>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone..." class="form-input w-full text-sm">
            </div>
            <select name="barangay" class="form-select text-sm">
                <option value="">All Barangays</option>
                @foreach($barangays as $b)
                <option value="{{ $b }}" {{ request('barangay') === $b ? 'selected' : '' }}>{{ $b }}</option>
                @endforeach
            </select>
            <select name="family_id" class="form-select text-sm">
                <option value="">All Families</option>
                @foreach($families as $f)
                <option value="{{ $f->id }}" {{ request('family_id') == $f->id ? 'selected' : '' }}>{{ $f->family_name }}</option>
                @endforeach
            </select>
            <select name="sacrament" class="form-select text-sm">
                <option value="">Any Sacrament</option>
                @foreach(\App\Models\SacramentalRecord::TYPES as $key => $label)
                <option value="{{ $key }}" {{ request('sacrament') === $key ? 'selected' : '' }}>Has {{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary text-sm">Search</button>
            @if(request()->hasAny(['search', 'barangay', 'family_id', 'sacrament']))
            <a href="{{ route('admin.parishioners.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Contact</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Barangay</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Family</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Sacraments</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($parishioners as $parishioner)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($parishioner->photo_path)
                                <img src="{{ Storage::url($parishioner->photo_path) }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                @else
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold text-xs">
                                    {{ substr($parishioner->first_name, 0, 1) }}{{ substr($parishioner->last_name, 0, 1) }}
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.parishioners.show', $parishioner) }}" class="font-medium text-gray-900 hover:text-blue-700">
                                        {{ $parishioner->full_name }}
                                    </a>
                                    @if($parishioner->is_head_of_family)
                                    <span class="ml-1 text-xs text-blue-600">(Head)</span>
                                    @endif
                                    @if($parishioner->birthdate)
                                    <p class="text-xs text-gray-400">Age {{ $parishioner->age }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <p>{{ $parishioner->contact_number ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $parishioner->email ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $parishioner->barangay ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($parishioner->family)
                            <a href="{{ route('admin.families.show', $parishioner->family) }}" class="hover:text-blue-700">{{ $parishioner->family->family_name }}</a>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-gray-600">{{ $parishioner->sacramental_records_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $parishioner->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $parishioner->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                <a href="{{ route('admin.parishioners.show', $parishioner) }}" class="action-btn action-btn-view">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                                <a href="{{ route('admin.parishioners.edit', $parishioner) }}" class="action-btn action-btn-edit">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            No parishioners found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($parishioners->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $parishioners->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
