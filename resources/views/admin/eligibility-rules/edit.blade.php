@extends('layouts.app')
@section('title', 'Edit Rule')
@section('page-title', 'Edit Eligibility Rule')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.eligibility-rules.index', $service) }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Rule</h1>
            <p class="text-sm text-gray-500">For: <strong>{{ $service->name }}</strong></p>
        </div>
    </div>
    @if($eligibilityRule->is_placeholder)
    <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 text-sm">
        ⚠ This is a <strong>placeholder</strong> rule seeded automatically. Review and update it to match your actual parish requirements. It will be unmarked as a placeholder once saved.
    </div>
    @endif
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.eligibility-rules.update', [$service, $eligibilityRule]) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('admin.eligibility-rules._form', ['eligibilityRule' => $eligibilityRule])
            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.eligibility-rules.index', $service) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
