@extends('layouts.app')

@section('title', 'Requirements — ' . $service->name)
@section('page-title', 'Requirement Templates')

@section('content')
<div class="py-6 max-w-4xl space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.services.requirements.index', $service) }}" class="hover:underline">Services</a>
                <span>/</span>
                <span class="font-semibold text-gray-900">{{ $service->name }}</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Requirement Templates</h1>
            <p class="text-sm text-gray-500 mt-0.5">Define what parishioners must provide before booking <strong>{{ $service->name }}</strong>.</p>
        </div>
        <a href="{{ route('admin.services.requirements.create', $service) }}" class="btn-primary text-sm">
            + Add Requirement
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        @if($requirements->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="font-medium">No requirement templates yet.</p>
            <a href="{{ route('admin.services.requirements.create', $service) }}" class="mt-2 inline-block text-blue-600 hover:underline text-sm">Add the first requirement</a>
        </div>
        @else
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Requirement Name</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Required?</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($requirements as $req)
                <tr class="hover:bg-gray-50 {{ !$req->is_active ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3 text-gray-400">{{ $req->sort_order }}</td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $req->name }}</p>
                        @if($req->description)
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $req->description }}</p>
                        @endif
                        @if($req->type === 'file' && $req->accepted_file_types)
                        <p class="text-xs text-blue-500 mt-0.5">Accepts: {{ $req->acceptedTypesLabel() }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 capitalize text-gray-600">{{ $req->type }}</td>
                    <td class="px-4 py-3">
                        @if($req->is_required)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Required</span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Optional</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($req->is_active)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Active</span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.services.requirements.edit', [$service, $req]) }}"
                               class="text-blue-600 hover:underline text-sm">Edit</a>
                            @if($req->is_active)
                            <form method="POST" action="{{ route('admin.services.requirements.destroy', [$service, $req]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline text-sm"
                                        onclick="return confirm('Deactivate this requirement?')">Deactivate</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
