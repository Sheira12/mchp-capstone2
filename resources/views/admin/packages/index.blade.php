@extends('layouts.app')
@section('title', 'Service Packages')
@section('page-title', 'Service Packages')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Service Packages</h1>
            <p class="text-sm text-gray-500 mt-0.5">Define packages parishioners can choose when booking a service.</p>
        </div>
        <a href="{{ route('admin.packages.create') }}" class="btn-primary text-sm">+ New Package</a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label">Filter by Service</label>
            <select name="service_id" class="form-select">
                <option value="">All Services</option>
                @foreach($services as $svc)
                <option value="{{ $svc->id }}" @selected(request('service_id')==$svc->id)>{{ $svc->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        <a href="{{ route('admin.packages.index') }}" class="btn-secondary text-sm">Clear</a>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        @if($packages->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="font-medium">No packages yet.</p>
            <a href="{{ route('admin.packages.create') }}" class="mt-2 inline-block text-blue-600 hover:underline text-sm">Create the first package</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Service</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Package Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Inclusions</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Price</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($packages as $pkg)
                    <tr class="hover:bg-gray-50 {{ !$pkg->is_active ? 'opacity-50' : '' }}">
                        <td class="px-4 py-3 text-gray-600 text-xs font-semibold">{{ $pkg->service->name }}</td>
                        <td class="px-4 py-3">
                            <p class="font-bold text-gray-900">{{ $pkg->name }}</p>
                            @if($pkg->description)
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $pkg->description }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs">
                            @if($pkg->inclusionsList())
                            <ul class="list-disc list-inside text-xs space-y-0.5">
                                @foreach(array_slice($pkg->inclusionsList(), 0, 3) as $inc)
                                <li>{{ $inc }}</li>
                                @endforeach
                                @if(count($pkg->inclusionsList()) > 3)
                                <li class="text-gray-400">+{{ count($pkg->inclusionsList()) - 3 }} more</li>
                                @endif
                            </ul>
                            @else
                            <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-bold text-gray-900">₱{{ number_format($pkg->price, 2) }}</td>
                        <td class="px-4 py-3">
                            @if($pkg->is_active)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Active</span>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.packages.edit', $pkg) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:underline text-sm"
                                            onclick="return confirm('Deactivate this package?')">
                                        {{ $pkg->is_active ? 'Deactivate' : 'Delete' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $packages->links() }}</div>
        @endif
    </div>
</div>
@endsection
