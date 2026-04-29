@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="py-6 max-w-2xl space-y-5">

    {{-- Parish Settings --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-5">Parish Information</h2>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Parish Name <span class="text-red-500">*</span></label>
                <input type="text" name="parish_name" value="{{ old('parish_name', $settings['parish_name']) }}" required
                       class="form-input w-full @error('parish_name') border-red-400 @enderror">
                @error('parish_name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Address <span class="text-red-500">*</span></label>
                <input type="text" name="parish_address" value="{{ old('parish_address', $settings['parish_address']) }}" required
                       class="form-input w-full">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="parish_phone" value="{{ old('parish_phone', $settings['parish_phone']) }}"
                           class="form-input w-full" placeholder="(049) XXX-XXXX">
                </div>
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="parish_email" value="{{ old('parish_email', $settings['parish_email']) }}"
                           class="form-input w-full">
                </div>
            </div>

            <div>
                <label class="form-label">Parish Priest</label>
                <input type="text" name="parish_priest" value="{{ old('parish_priest', $settings['parish_priest']) }}"
                       class="form-input w-full" placeholder="Rev. Fr. Name">
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Save Settings</button>
            </div>
        </form>
    </div>

    {{-- System Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">System Information</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-gray-500">Laravel Version</dt>
                <dd class="font-medium text-gray-900">{{ app()->version() }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">PHP Version</dt>
                <dd class="font-medium text-gray-900">{{ PHP_VERSION }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Environment</dt>
                <dd class="font-medium text-gray-900">{{ app()->environment() }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-500">Timezone</dt>
                <dd class="font-medium text-gray-900">{{ config('app.timezone') }}</dd>
            </div>
        </dl>
    </div>

    {{-- Cache Management --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Cache Management</h2>
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.settings.clear-cache') }}">
                @csrf
                <button type="submit" class="btn-secondary text-sm">Clear Config Cache</button>
            </form>
            <form method="POST" action="{{ route('admin.settings.clear-cache') }}">
                @csrf
                <input type="hidden" name="type" value="view">
                <button type="submit" class="btn-secondary text-sm">Clear View Cache</button>
            </form>
        </div>
        <p class="text-xs text-gray-400 mt-3">Clearing cache may be needed after updating settings or deploying changes.</p>
    </div>
</div>
@endsection
