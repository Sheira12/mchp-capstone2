@extends('layouts.app')

@section('title', 'Families')
@section('page-title', 'Families')

@section('content')
<div class="py-6 space-y-4">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search family name or barangay…"
                   class="form-input text-sm w-64">
            <button type="submit" class="btn-secondary text-sm">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.families.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
        </form>
        <a href="{{ route('admin.families.create') }}" class="btn-primary text-sm">+ New Family</a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Family Name</th>
                    <th class="px-4 py-3 font-medium">Address</th>
                    <th class="px-4 py-3 font-medium">Barangay</th>
                    <th class="px-4 py-3 font-medium">Members</th>
                    <th class="px-4 py-3 font-medium">Contact</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($families as $family)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('admin.families.show', $family) }}" class="hover:text-blue-700">
                            {{ $family->family_name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $family->address ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $family->barangay ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $family->members_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $family->contact_number ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.families.show', $family) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="{{ route('admin.families.edit', $family) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.families.destroy', $family) }}"
                                  onsubmit="return confirm('Delete this family? Members will be unlinked.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No families found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $families->links() }}
        </div>
    </div>
</div>
@endsection
