@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="py-6 max-w-2xl space-y-5">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Parish Information ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            Parish Information
        </h2>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Parish Name <span class="text-red-500">*</span></label>
                <input type="text" name="parish_name" value="{{ old('parish_name', $settings['parish_name']) }}"
                       required class="form-input w-full @error('parish_name') border-red-400 @enderror">
                @error('parish_name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Address <span class="text-red-500">*</span></label>
                <input type="text" name="parish_address" value="{{ old('parish_address', $settings['parish_address']) }}"
                       required class="form-input w-full">
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
                <button type="submit" class="btn-primary">Save Parish Settings</button>
            </div>
        </form>
    </div>

    {{-- ── Social Media Links ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
            Social Media Links
        </h2>
        <p class="text-xs text-gray-400 mb-5">These links appear in the public website footer. Leave blank to hide an icon.</p>

        <form method="POST" action="{{ route('admin.settings.update-socials') }}" class="space-y-4">
            @csrf @method('PUT')

            {{-- Facebook --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                </div>
                <div class="flex-1">
                    <label class="form-label text-xs">Facebook Page URL</label>
                    <input type="url" name="social_facebook" value="{{ old('social_facebook', $socials['facebook']) }}"
                           class="form-input w-full text-sm" placeholder="https://facebook.com/yourpage">
                </div>
            </div>

            {{-- Messenger --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.145 2 11.243c0 2.898 1.376 5.489 3.534 7.196V22l3.308-1.81A11.07 11.07 0 0012 20.486c5.523 0 10-4.145 10-9.243S17.523 2 12 2zm1.05 12.45l-2.545-2.7-4.97 2.7 5.47-5.8 2.6 2.7 4.915-2.7-5.47 5.8z"/></svg>
                </div>
                <div class="flex-1">
                    <label class="form-label text-xs">Messenger URL</label>
                    <input type="url" name="social_messenger" value="{{ old('social_messenger', $socials['messenger']) }}"
                           class="form-input w-full text-sm" placeholder="https://m.me/yourpage">
                </div>
            </div>

            {{-- Instagram --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-yellow-400 via-pink-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z" fill="#fff"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div class="flex-1">
                    <label class="form-label text-xs">Instagram URL</label>
                    <input type="url" name="social_instagram" value="{{ old('social_instagram', $socials['instagram']) }}"
                           class="form-input w-full text-sm" placeholder="https://instagram.com/yourpage">
                </div>
            </div>

            {{-- YouTube --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-red-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 001.46 6.42 29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="#fff"/></svg>
                </div>
                <div class="flex-1">
                    <label class="form-label text-xs">YouTube Channel URL</label>
                    <input type="url" name="social_youtube" value="{{ old('social_youtube', $socials['youtube']) }}"
                           class="form-input w-full text-sm" placeholder="https://youtube.com/@yourchannel">
                </div>
            </div>

            {{-- TikTok --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-black flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.76a4.85 4.85 0 01-1.01-.07z"/></svg>
                </div>
                <div class="flex-1">
                    <label class="form-label text-xs">TikTok URL</label>
                    <input type="url" name="social_tiktok" value="{{ old('social_tiktok', $socials['tiktok']) }}"
                           class="form-input w-full text-sm" placeholder="https://tiktok.com/@yourpage">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Save Social Media Links</button>
            </div>
        </form>
    </div>

    {{-- ── System Info ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">System Information</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500">Laravel Version</dt><dd class="font-medium">{{ app()->version() }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">PHP Version</dt><dd class="font-medium">{{ PHP_VERSION }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Environment</dt><dd class="font-medium">{{ app()->environment() }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Timezone</dt><dd class="font-medium">{{ config('app.timezone') }}</dd></div>
        </dl>
    </div>

    {{-- ── Cache Management ── --}}
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
