@extends('layouts.app')
@section('title', 'Eligibility Rules — ' . $service->name)
@section('page-title', 'Eligibility Rules')

@section('content')
<div class="py-6 max-w-5xl space-y-5">

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="text-xs text-gray-400 mb-1">
                Services &rsaquo; <span class="font-semibold text-gray-700">{{ $service->name }}</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Eligibility Rules</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Define what a parishioner must satisfy before booking <strong>{{ $service->name }}</strong>.
            </p>
        </div>
        <a href="{{ route('admin.eligibility-rules.create', $service) }}" class="btn-primary text-sm">+ Add Rule</a>
    </div>

    @if($rules->where('is_placeholder', true)->count() > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 flex items-start gap-3 text-sm text-amber-800">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <div>
            <span class="font-bold">⚠ Placeholder rules are active.</span>
            {{ $rules->where('is_placeholder', true)->count() }} rule(s) on this service are auto-seeded placeholders.
            Review and edit them to match the real parish requirements before going live.
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        @if($rules->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-medium">No eligibility rules yet — this service is open to all.</p>
            <a href="{{ route('admin.eligibility-rules.create', $service) }}" class="mt-2 inline-block text-blue-600 hover:underline text-sm">Add the first rule</a>
        </div>
        @else
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">#</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Rule</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Applies To</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Required?</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rules as $rule)
                <tr class="hover:bg-gray-50 {{ !$rule->is_active ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $rule->sort_order }}</td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $rule->name }}</p>
                        @if($rule->description)
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $rule->description }}</p>
                        @endif
                        @if($rule->is_placeholder)
                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">⚠ Placeholder</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $rule->getCriterionLabel() }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $rule->getAppliesToLabel() }}</td>
                    <td class="px-4 py-3">
                        @if($rule->is_required)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Required</span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Recommended</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($rule->is_active)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Active</span>
                        @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.eligibility-rules.edit', [$service, $rule]) }}"
                               class="text-blue-600 hover:underline text-sm">Edit</a>
                            @if($rule->is_active)
                            <form method="POST" action="{{ route('admin.eligibility-rules.destroy', [$service, $rule]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:underline text-sm"
                                        onclick="return confirm('Deactivate this rule?')">Deactivate</button>
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
