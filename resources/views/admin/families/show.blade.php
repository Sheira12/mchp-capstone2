@extends('layouts.app')

@section('title', $family->family_name)
@section('page-title', $family->family_name)

@section('content')
<div class="py-6 space-y-5">

    {{-- Header actions --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.families.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Families</a>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('admin.families.edit', $family) }}" class="btn-secondary text-sm">Edit</a>
            <form method="POST" action="{{ route('admin.families.destroy', $family) }}"
                  onsubmit="return confirm('Delete this family?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger text-sm">Delete</button>
            </form>
        </div>
    </div>

    {{-- Family Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Family Information</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Family Name</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $family->family_name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Contact Number</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $family->contact_number ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Address</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $family->address ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Barangay</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $family->barangay ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">City / Municipality</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $family->city ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Province</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $family->province ?? '—' }}</dd>
            </div>
            @if($family->notes)
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Notes</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $family->notes }}</dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Members --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Family Members ({{ $family->members->count() }})</h2>
            <a href="{{ route('admin.parishioners.create') }}?family_id={{ $family->id }}" class="btn-primary text-sm">+ Add Member</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 font-medium">Birthdate</th>
                    <th class="px-4 py-3 font-medium">Contact</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($family->members as $member)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($member->photo_path)
                                <img src="{{ Storage::url($member->photo_path) }}" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700">
                                    {{ substr($member->first_name, 0, 1) }}
                                </div>
                            @endif
                            <a href="{{ route('admin.parishioners.show', $member) }}" class="font-medium text-gray-900 hover:text-blue-700">
                                {{ $member->full_name }}
                            </a>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if($member->is_head_of_family)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Head</span>
                        @else
                            <span class="text-gray-400 text-xs">Member</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $member->birthdate ? $member->birthdate->format('M d, Y') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $member->contact_number ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.parishioners.show', $member) }}" class="action-btn action-btn-view">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">No members yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
