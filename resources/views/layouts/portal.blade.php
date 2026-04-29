<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My Portal') — {{ config('parish.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        body { background: #f1f5f9; }
        .portal-sidebar { width: 260px; flex-shrink: 0; }
        .portal-nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 0.625rem 0.875rem; border-radius: 0.625rem;
            font-size: 0.875rem; font-weight: 500; color: #475569;
            text-decoration: none; transition: all 0.2s;
        }
        .portal-nav-item:hover { background: #f1f5f9; color: #2563eb; }
        .portal-nav-item.active { background: #eff6ff; color: #2563eb; font-weight: 600; }
        .portal-nav-item.active svg { color: #2563eb; }
        .portal-nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .portal-nav-section { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #94a3b8; padding: 0.75rem 0.875rem 0.25rem; }
        #mobile-sidebar { transform: translateX(-100%); transition: transform 0.3s ease; }
        #mobile-sidebar.open { transform: translateX(0); }
    </style>
</head>
<body class="h-full font-sans antialiased">

{{-- Mobile sidebar overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="closeSidebar()"></div>

{{-- Mobile Sidebar --}}
<div id="mobile-sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white shadow-2xl lg:hidden flex flex-col">
    @include('layouts.portal-sidebar')
</div>

<div class="min-h-full flex flex-col">

    {{-- Top Navigation --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Left: hamburger + logo --}}
                <div class="flex items-center gap-3">
                    <button onclick="openSidebar()" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('images/parish-logo.png') }}" alt="Logo" class="w-8 h-8 rounded-full object-cover">
                        <div class="hidden sm:block">
                            <p class="text-sm font-bold text-gray-900 leading-tight">MHC Parish</p>
                            <p class="text-xs text-gray-400 leading-tight">Parishioner Portal</p>
                        </div>
                    </a>
                </div>

                {{-- Center: page title --}}
                <h1 class="text-base font-bold text-gray-800 hidden md:block">@yield('title', 'Dashboard')</h1>

                {{-- Right: user menu --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="hidden sm:flex items-center gap-1.5 text-sm text-gray-500 hover:text-blue-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Website
                    </a>
                    <div class="relative" x-data="{ open: false }">
                        <button onclick="toggleUserMenu()" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-gray-100 transition">
                            @if(auth()->user()->parishioner?->photo_path)
                                <img src="{{ Storage::url(auth()->user()->parishioner->photo_path) }}" class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-bold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            @endif
                            <span class="hidden sm:block text-sm font-medium text-gray-700 max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-1 z-50">
                            <a href="{{ route('parishioner.profile') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                My Profile
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Layout --}}
    <div class="flex flex-1 max-w-screen-xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 gap-6">

        {{-- Desktop Sidebar --}}
        <aside class="portal-sidebar hidden lg:flex flex-col gap-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                @include('layouts.portal-sidebar')
            </div>
        </aside>

        {{-- Content --}}
        <main class="flex-1 min-w-0">
            {{-- Flash messages --}}
            @if(session('success'))
            <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3.5 text-sm shadow-sm">
                <svg class="w-5 h-5 flex-shrink-0 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('info'))
            <div class="mb-5 flex items-center gap-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-5 py-3.5 text-sm shadow-sm">
                <svg class="w-5 h-5 flex-shrink-0 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                {{ session('info') }}
            </div>
            @endif
            @if($errors->any())
            <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3.5 text-sm shadow-sm">
                <svg class="w-5 h-5 flex-shrink-0 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <ul class="space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
function openSidebar() {
    document.getElementById('mobile-sidebar').classList.add('open');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
}
function closeSidebar() {
    document.getElementById('mobile-sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.add('hidden');
}
function toggleUserMenu() {
    document.getElementById('user-menu').classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const menu = document.getElementById('user-menu');
    if (!e.target.closest('[onclick="toggleUserMenu()"]') && menu) {
        menu.classList.add('hidden');
    }
});
</script>

@stack('scripts')
</body>
</html>
