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
                       required class="form-input w-full @error('parish_address') border-red-400 @enderror">
                @error('parish_address')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="parish_phone" value="{{ old('parish_phone', $settings['parish_phone']) }}"
                           class="form-input w-full @error('parish_phone') border-red-400 @enderror" placeholder="(049) XXX-XXXX">
                    @error('parish_phone')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email Address</label>
                    <input type="email" name="parish_email" value="{{ old('parish_email', $settings['parish_email']) }}"
                           class="form-input w-full @error('parish_email') border-red-400 @enderror">
                    @error('parish_email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="form-label">Parish Priest</label>
                <input type="text" name="parish_priest" value="{{ old('parish_priest', $settings['parish_priest']) }}"
                       class="form-input w-full @error('parish_priest') border-red-400 @enderror" placeholder="Rev. Fr. Name">
                @error('parish_priest')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Parish Secretary</label>
                    <input type="text" name="parish_secretary" value="{{ old('parish_secretary', $settings['parish_secretary']) }}"
                           class="form-input w-full @error('parish_secretary') border-red-400 @enderror" placeholder="Full name of the Parish Secretary">
                    @error('parish_secretary')<p class="form-error">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Appears on the signature line of all certificates.</p>
                </div>
                <div>
                    <label class="form-label">Finance Officer</label>
                    <input type="text" name="parish_finance_officer" value="{{ old('parish_finance_officer', $settings['parish_finance_officer']) }}"
                           class="form-input w-full @error('parish_finance_officer') border-red-400 @enderror" placeholder="Full name of the Finance Officer">
                    @error('parish_finance_officer')<p class="form-error">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-400 mt-1">Appears on financial report signatures.</p>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Save Parish Settings</button>
            </div>
        </form>
    </div>

    {{-- ── Parish Media (Logo + Banner) ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Parish Media
        </h2>
        <p class="text-xs text-gray-400 mb-5">Upload a parish logo (used in emails, receipts, certificates) and a church background banner (used on the public homepage hero). Images are stored on Supabase — they will persist across redeploys.</p>

        <form method="POST" action="{{ route('admin.settings.update-media') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Parish Logo --}}
            <div class="flex flex-wrap items-start gap-5">
                <div class="flex-shrink-0">
                    @php $logoUrl = $media['parish_logo'] ? \App\Helpers\MediaHelper::url($media['parish_logo']) : asset('images/parish-logo.png'); @endphp
                    <img src="{{ $logoUrl }}" alt="Parish Logo"
                         class="w-20 h-20 rounded-full object-cover border-2 border-blue-200 shadow"
                         onerror="this.src='{{ asset('images/parish-logo.png') }}'">
                </div>
                <div class="flex-1 min-w-0">
                    <label class="form-label">Parish Logo</label>
                    <input type="file" name="parish_logo" accept="image/*"
                           class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">Recommended: 300×300 px square. Max 3 MB. JPG, PNG, WebP.</p>
                    @if($media['parish_logo'])
                    <p class="text-xs text-green-600 mt-1 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Custom logo active — stored on Supabase
                    </p>
                    @endif
                </div>
            </div>

            {{-- Church Banner --}}
            <div class="flex flex-wrap items-start gap-5">
                <div class="flex-shrink-0">
                    @php $bannerUrl = $media['church_banner'] ? \App\Helpers\MediaHelper::url($media['church_banner']) : asset('images/church-bg.jpg'); @endphp
                    <img src="{{ $bannerUrl }}" alt="Church Banner"
                         class="w-32 h-20 rounded-lg object-cover border border-gray-200 shadow"
                         onerror="this.src='{{ asset('images/church-bg.jpg') }}'">
                </div>
                <div class="flex-1 min-w-0">
                    <label class="form-label">Church Background Banner</label>
                    <input type="file" name="church_banner" accept="image/*"
                           class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">Recommended: 1920×1080 px landscape. Max 5 MB. Used as the blurred hero background on the homepage.</p>
                    @if($media['church_banner'])
                    <p class="text-xs text-green-600 mt-1 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Custom banner active — stored on Supabase
                    </p>
                    @endif
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Upload Media</button>
            </div>
        </form>
    </div>

    {{-- ── Office Hours ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Office Hours
        </h2>
        <p class="text-xs text-gray-400 mb-4">Displayed on the Contact page and the Mass Schedule page.</p>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf @method('PUT')
            {{-- Pass all existing settings through so they're not cleared --}}
            <input type="hidden" name="parish_name"            value="{{ $settings['parish_name'] }}">
            <input type="hidden" name="parish_address"         value="{{ $settings['parish_address'] }}">
            <input type="hidden" name="parish_phone"           value="{{ $settings['parish_phone'] }}">
            <input type="hidden" name="parish_email"           value="{{ $settings['parish_email'] }}">
            <input type="hidden" name="parish_priest"          value="{{ $settings['parish_priest'] }}">
            <input type="hidden" name="parish_secretary"       value="{{ $settings['parish_secretary'] }}">
            <input type="hidden" name="parish_finance_officer" value="{{ $settings['parish_finance_officer'] }}">
            <div>
                <label class="form-label">Office Hours</label>
                <input type="text" name="office_hours"
                       value="{{ old('office_hours', $settings['office_hours']) }}"
                       class="form-input w-full"
                       placeholder="e.g. Mon–Fri 8:00 AM – 5:00 PM, Sat 8:00 AM – 12:00 PM">
                <p class="text-xs text-gray-400 mt-1">Single line of text. Leave blank to hide.</p>
            </div>
            <div class="pt-1">
                <button type="submit" class="btn-primary">Save Office Hours</button>
            </div>
        </form>
    </div>

    {{-- ── Social Media Links ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">        <h2 class="text-lg font-semibold text-gray-800 mb-2 flex items-center gap-2">
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
                           class="form-input w-full text-sm @error('social_facebook') border-red-400 @enderror" placeholder="https://facebook.com/yourpage">
                    @error('social_facebook')<p class="form-error text-xs">{{ $message }}</p>@enderror
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
                           class="form-input w-full text-sm @error('social_messenger') border-red-400 @enderror" placeholder="https://m.me/yourpage">
                    @error('social_messenger')<p class="form-error text-xs">{{ $message }}</p>@enderror
                </div>
            </div>
            {{-- Instagram --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-yellow-400 via-pink-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="5"/>
                        <circle cx="12" cy="12" r="4"/>
                        <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <label class="form-label text-xs">Instagram URL</label>
                    <input type="url" name="social_instagram" value="{{ old('social_instagram', $socials['instagram']) }}"
                           class="form-input w-full text-sm @error('social_instagram') border-red-400 @enderror" placeholder="https://instagram.com/yourpage">
                    @error('social_instagram')<p class="form-error text-xs">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- YouTube --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#FF0000" d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.6 3.5 12 3.5 12 3.5s-7.6 0-9.4.6a3 3 0 0 0-2.1 2.1C0 8 0 12 0 12s0 4 .5 5.8a3 3 0 0 0 2.1 2.1c1.8.6 9.4.6 9.4.6s7.6 0 9.4-.6a3 3 0 0 0 2.1-2.1c.5-1.8.5-5.8.5-5.8s0-4-.5-5.8Z"/>
                        <path fill="#fff" d="M9.6 15.5 15.8 12 9.6 8.5v7Z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <label class="form-label text-xs">YouTube Channel URL</label>
                    <input type="url" name="social_youtube" value="{{ old('social_youtube', $socials['youtube']) }}"
                           class="form-input w-full text-sm @error('social_youtube') border-red-400 @enderror" placeholder="https://youtube.com/@yourchannel">
                    @error('social_youtube')<p class="form-error text-xs">{{ $message }}</p>@enderror
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
                           class="form-input w-full text-sm @error('social_tiktok') border-red-400 @enderror" placeholder="https://tiktok.com/@yourpage">
                    @error('social_tiktok')<p class="form-error text-xs">{{ $message }}</p>@enderror
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
                <input type="hidden" name="type" value="config">
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
