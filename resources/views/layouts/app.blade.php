<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('parish.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/parish-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
    /* ═══════════════════════════════════════════
       ADMIN LAYOUT — GLOBAL RESPONSIVE OVERRIDES
       ═══════════════════════════════════════════ */

    /* Prevent horizontal scroll on all screen sizes */
    body { overflow-x: hidden; }
    main.flex-1.px-6 { max-width: 100%; overflow-x: hidden; }
    @media (min-width: 1280px) {
        main.flex-1.px-6 > .py-6 { max-width: 1200px; margin: 0 auto; }
    }

    /* Mobile: reduce padding */
    @media (max-width: 639px) {
        main.flex-1.px-6 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .py-6 { padding-top: 1rem; padding-bottom: 1rem; }
    }

    /* Admin grid overrides */
    @media (max-width: 639px) {
        .grid.grid-cols-1.sm\:grid-cols-2 { grid-template-columns: 1fr !important; }
        .grid.grid-cols-2 { grid-template-columns: 1fr !important; }
        dl.grid.grid-cols-2 { grid-template-columns: 1fr !important; }
    }
    @media (min-width: 640px) {
        .grid.grid-cols-1.sm\:grid-cols-2 { grid-template-columns: repeat(2, 1fr) !important; }
    }
    @media (max-width: 1023px) {
        .grid.grid-cols-1.lg\:grid-cols-2 { grid-template-columns: 1fr !important; }
        .grid.grid-cols-1.lg\:grid-cols-3 { grid-template-columns: 1fr !important; }
        .lg\:col-span-2 { grid-column: span 1 !important; }
    }
    @media (min-width: 1024px) {
        .grid.grid-cols-1.lg\:grid-cols-2 { grid-template-columns: repeat(2, 1fr) !important; }
        .grid.grid-cols-1.lg\:grid-cols-3 { grid-template-columns: repeat(3, 1fr) !important; }
        .lg\:col-span-2 { grid-column: span 2 !important; }
    }

    /* Stat cards: 2 cols on mobile, 4 on desktop */
    .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 {
        grid-template-columns: repeat(2, 1fr) !important;
    }
    @media (min-width: 1024px) {
        .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 {
            grid-template-columns: repeat(4, 1fr) !important;
        }
    }

    /* Tables: horizontal scroll */
    .overflow-x-auto { overflow-x: auto; -webkit-overflow-scrolling: touch; }

    /* Cards: no overflow */
    .bg-white.rounded-xl, .bg-white.rounded-2xl { word-break: break-word; }

    /* Font scaling */
    @media (max-width: 479px) {
        .text-3xl { font-size: 1.5rem !important; }
        .text-2xl { font-size: 1.25rem !important; }
        h1.text-xl { font-size: 1rem !important; }
    }

    /* Topbar: hide "View Website" text on mobile */
    @media (max-width: 639px) {
        header .text-sm.text-blue-600 { font-size: 0.75rem; }
    }

    /* Mobile bottom nav for admin */
    .admin-bottom-nav {
        display: none;
        position: fixed; bottom: 0; left: 0; right: 0;
        background: #1e3a8a; z-index: 45;
        border-top: 1px solid rgba(255,255,255,0.1);
        padding-bottom: env(safe-area-inset-bottom, 0);
    }
    .admin-bottom-nav-inner {
        display: flex; align-items: stretch; justify-content: space-around; height: 52px;
    }
    .abn-item {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 2px; flex: 1; text-decoration: none;
        color: rgba(255,255,255,0.6); font-size: 0.58rem; font-weight: 600;
        border: none; background: none; cursor: pointer; transition: color 0.15s;
    }
    .abn-item.active { color: #fff; }
    .abn-item svg { width: 18px; height: 18px; }
    @media (max-width: 1023px) {
        .admin-bottom-nav { display: block; }
        main.flex-1.px-6 { padding-bottom: 72px; }
    }
    </style>
</head>
<body class="h-full font-sans antialiased">
<div class="min-h-full flex">

    {{-- Sidebar --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-blue-900 text-white flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300">
        {{-- Logo / Brand --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-blue-800 bg-blue-950">
            <div class="relative flex-shrink-0">
                <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo"
                     class="w-12 h-12 rounded-full object-cover border-2 border-yellow-400 shadow-lg">
                <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-green-400 rounded-full border-2 border-blue-950"></span>
            </div>
            <div class="leading-tight min-w-0">
                <p class="text-sm font-bold text-white truncate">MHC Parish</p>
                <p class="text-xs text-yellow-300 font-medium">Admin Portal</p>
                <p class="text-xs text-blue-400 truncate">{{ auth()->user()->name }}</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <div class="pt-2 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Parishioners</div>
            <a href="{{ route('admin.parishioners.index') }}" class="nav-link {{ request()->routeIs('admin.parishioners.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Parishioners
            </a>
            <a href="{{ route('admin.families.index') }}" class="nav-link {{ request()->routeIs('admin.families.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Families
            </a>

            <div class="pt-2 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Records</div>
            <a href="{{ route('admin.sacramental-records.index') }}" class="nav-link {{ request()->routeIs('admin.sacramental-records.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Sacramental Records
            </a>
            <a href="{{ route('admin.certificates.index') }}" class="nav-link {{ request()->routeIs('admin.certificates.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Certificates
            </a>

            <div class="pt-2 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Services</div>
            <a href="{{ route('admin.bookings.index') }}" class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Bookings
            </a>
            <a href="{{ route('admin.bookings.calendar') }}" class="nav-link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Calendar
            </a>
            <a href="{{ route('walkin.index') }}" target="_blank" class="nav-link" style="background:rgba(212,175,55,0.12);border:1px solid rgba(212,175,55,0.3);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span style="color:#fde68a;font-weight:600;">Walk-in Kiosk</span>
            </a>

            @role('super_admin|finance_officer')
            <div class="pt-2 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Finance</div>
            <a href="{{ route('admin.payments.index') }}" class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Payments
            </a>
            <a href="{{ route('admin.ledger.index') }}" class="nav-link {{ request()->routeIs('admin.ledger.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Credit &amp; Debit Ledger
            </a>
            <a href="{{ route('admin.payments.report') }}" class="nav-link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Financial Reports
            </a>
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Reports Module
            </a>
            @endrole

            <div class="pt-2 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">Website</div>
            <a href="{{ route('admin.announcements.index') }}" class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                Announcements
            </a>
            <a href="{{ route('admin.mass-schedules.index') }}" class="nav-link {{ request()->routeIs('admin.mass-schedules.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Mass Schedules
            </a>
            <a href="{{ route('admin.events.index') }}" class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Events
            </a>
            <a href="{{ route('admin.gallery.index') }}" class="nav-link {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Gallery
            </a>
            <a href="{{ route('admin.livestreams.index') }}" class="nav-link {{ request()->routeIs('admin.livestreams.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.876V15.124a1 1 0 01-1.447.895L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Livestream
            </a>

            @role('super_admin')
            <div class="pt-2 pb-1 px-3 text-xs font-semibold text-blue-400 uppercase tracking-wider">System</div>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Users
            </a>
            <a href="{{ route('admin.audit-logs.index') }}" class="nav-link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Audit Logs
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-link">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
            @endrole
        </nav>

        {{-- User info --}}
        <div class="border-t border-blue-800 p-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-blue-300 truncate">{{ auth()->user()->getRoleNames()->first() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-blue-300 hover:text-white" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
        {{-- Top bar --}}
        <header class="bg-white shadow-sm sticky top-0 z-40 border-b border-gray-100">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-3">
                    {{-- Mobile hamburger --}}
                    <button id="sidebar-toggle" class="lg:hidden text-gray-500 hover:text-gray-700 p-1.5 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    {{-- Mobile logo (hidden on desktop since sidebar is visible) --}}
                    <div class="flex items-center gap-2 lg:hidden">
                        <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo" class="w-8 h-8 rounded-full object-cover border border-blue-200">
                        <span class="text-sm font-bold text-blue-900 hidden sm:block">MHC Parish</span>
                    </div>
                    <h1 class="text-base font-semibold text-gray-800 hidden lg:block">@yield('page-title', 'Dashboard')</h1>
                    <h1 class="text-sm font-semibold text-gray-700 lg:hidden">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" target="_blank"
                       class="hidden sm:flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        View Website
                    </a>

                    {{-- ── Notification Bell ── --}}
                    <div class="relative" id="notif-wrap">
                        <button id="notif-btn" onclick="toggleNotifPanel()"
                                class="relative p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            {{-- Badge --}}
                            <span id="notif-badge"
                                  class="absolute -top-0.5 -right-0.5 hidden w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center leading-none">
                                0
                            </span>
                        </button>

                        {{-- Dropdown panel --}}
                        <div id="notif-panel"
                             class="hidden absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 bg-gray-50">
                                <span class="text-sm font-bold text-gray-800">Notifications</span>
                                <button onclick="markAllRead()" class="text-xs text-blue-600 hover:underline font-medium">Mark all read</button>
                            </div>
                            <div id="notif-list" class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                <div class="px-4 py-8 text-center text-sm text-gray-400">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    No new notifications
                                </div>
                            </div>
                            <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}"
                               class="block text-center text-xs font-semibold text-blue-600 hover:text-blue-800 py-2.5 border-t border-gray-100 hover:bg-blue-50 transition">
                                Pending bookings →
                            </a>
                            <a href="{{ route('admin.certificates.index', ['status' => 'draft']) }}"
                               class="block text-center text-xs font-semibold text-purple-600 hover:text-purple-800 py-2.5 border-t border-gray-100 hover:bg-purple-50 transition">
                                Certificate requests →
                            </a>
                        </div>
                    </div>

                    {{-- Role badge --}}
                    <span class="hidden md:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 capitalize">
                        {{ auth()->user()->getRoleNames()->first() }}
                    </span>
                </div>

                {{-- ── Toast notification popup ── --}}
                <div id="toast-container" class="fixed top-16 right-4 z-50 space-y-2 pointer-events-none"></div>
            </div>
        </header>

        {{-- Flash messages --}}
        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="alert-success mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error') || $errors->any())
                <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <div>
                        @if(session('error'))
                            <p>{{ session('error') }}</p>
                        @endif
                        @if($errors->any())
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Page content --}}
        <main class="flex-1 px-6 pb-8 lg:pb-8 pb-safe">
            @yield('content')
        </main>

        <footer class="text-center text-xs text-gray-400 py-5 mt-4 border-t border-gray-100 bg-white">
            &copy; {{ date('Y') }} {{ config('parish.name') }} &mdash; All rights reserved.
        </footer>
    </div>
</div>

{{-- Sidebar overlay for mobile --}}
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

{{-- Mobile bottom nav for admin (shown on < 1024px) --}}
<nav class="admin-bottom-nav">
    <div class="admin-bottom-nav-inner">
        <a href="{{ route('admin.dashboard') }}" class="abn-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>
        <a href="{{ route('admin.parishioners.index') }}" class="abn-item {{ request()->routeIs('admin.parishioners.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Parishioners
        </a>
        <a href="{{ route('admin.bookings.index') }}" class="abn-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Bookings
        </a>
        <a href="{{ route('admin.certificates.index') }}" class="abn-item {{ request()->routeIs('admin.certificates.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Certs
        </a>
        <button class="abn-item" onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full'); document.getElementById('sidebar-overlay').classList.toggle('hidden');">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            More
        </button>
    </div>
</nav>

<script>
    // ── Sidebar toggle ──
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const toggle  = document.getElementById('sidebar-toggle');

    toggle?.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });

    overlay?.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });

    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert-success').forEach(el => el.remove());
    }, 5000);

    // ── Notification Bell System ────────────────────────────────────
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let lastCount = 0;
    let seenIds   = new Set();
    let panelOpen = false;

    function toggleNotifPanel() {
        panelOpen = !panelOpen;
        document.getElementById('notif-panel').classList.toggle('hidden', !panelOpen);
    }

    // Close panel when clicking outside
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('notif-wrap');
        if (wrap && !wrap.contains(e.target)) {
            document.getElementById('notif-panel').classList.add('hidden');
            panelOpen = false;
        }
    });

    // Render notifications in the dropdown panel
    function renderNotifList(notifications) {
        const list = document.getElementById('notif-list');
        if (!notifications.length) {
            list.innerHTML = `<div class="px-4 py-8 text-center text-sm text-gray-400">
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                No new notifications</div>`;
            return;
        }
        list.innerHTML = notifications.map(n => `
            <a href="${n.url}" onclick="markRead('${n.id}')"
               class="flex items-start gap-3 px-4 py-3 hover:bg-blue-50 transition cursor-pointer block">
                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="${n.data.notif_type === 'certificate_request' ? 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z' : 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'}"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 font-medium leading-snug">${n.data.message}</p>
                    <p class="text-xs text-gray-400 mt-0.5">${n.created_at}</p>
                </div>
                <div class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-2"></div>
            </a>
        `).join('');
    }

    // Show a toast popup for a new notification
    function showToast(notif) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-start gap-3 bg-white border border-gray-200 rounded-xl shadow-xl px-4 py-3 w-80 transform translate-x-full opacity-0 transition-all duration-300';
        toast.innerHTML = `
            <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-gray-700 uppercase tracking-wide">New Booking</p>
                <p class="text-sm text-gray-800 leading-snug mt-0.5">${notif.data.message}</p>
                <a href="${notif.url}" class="text-xs text-blue-600 hover:underline font-semibold mt-1 inline-block">View booking →</a>
            </div>
            <button onclick="this.closest('div.pointer-events-auto').remove()" class="text-gray-300 hover:text-gray-500 flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        `;
        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            });
        });

        // Auto-dismiss after 6s
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 6000);
    }

    // Update badge count
    function updateBadge(count) {
        const badge = document.getElementById('notif-badge');
        if (count > 0) {
            badge.textContent = count > 9 ? '9+' : count;
            badge.classList.remove('hidden');
            badge.classList.add('flex');
        } else {
            badge.classList.add('hidden');
            badge.classList.remove('flex');
        }
    }

    // Fetch unread notifications from API
    async function fetchNotifications() {
        try {
            const res = await fetch('{{ route("admin.notifications.unread") }}', {
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();

            updateBadge(data.count);
            renderNotifList(data.notifications);

            // Show toast for any NEW notifications since last poll
            if (data.count > 0) {
                data.notifications.forEach(n => {
                    if (!seenIds.has(n.id)) {
                        seenIds.add(n.id);
                        // Only show toast if this is truly new (not on first load)
                        if (lastCount !== null) {
                            showToast(n);
                        }
                    }
                });
            }
            lastCount = data.count;

        } catch (e) {
            // Silent fail — don't break the admin if notification API is down
        }
    }

    // Mark single notification as read
    async function markRead(id) {
        try {
            await fetch(`/admin/notifications/${id}/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            seenIds.delete(id);
            setTimeout(fetchNotifications, 300);
        } catch(e) {}
    }

    // Mark all as read
    async function markAllRead() {
        try {
            await fetch('{{ route("admin.notifications.read-all") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            seenIds.clear();
            lastCount = 0;
            updateBadge(0);
            renderNotifList([]);
            document.getElementById('notif-panel').classList.add('hidden');
            panelOpen = false;
        } catch(e) {}
    }

    // Initial load then poll every 15 seconds
    // Set lastCount to null on first load so no toast is shown for existing notifications
    lastCount = null;
    fetchNotifications().then(() => { lastCount = 0; });
    setInterval(fetchNotifications, 15000);
</script>

@stack('scripts')
</body>
</html>
