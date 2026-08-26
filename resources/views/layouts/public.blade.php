<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') &mdash; {{ config('parish.name') }}</title>
    <meta name="description" content="@yield('meta-description', 'Mary Help of Christians Parish - Southville 1, Niugan, Cabuyao, Laguna')">
    <link rel="icon" type="image/png" href="{{ asset('images/parish-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
    /* Global */
    * { box-sizing: border-box; }
    img { max-width: 100%; height: auto; }
    body { overflow-x: hidden; }
    section, .max-w-7xl { max-width: 100%; }

    /* Navbar */
    .pub-nav {
        position: sticky; top: 0; z-index: 50;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(226,232,240,0.8);
        transition: box-shadow 0.25s, background 0.25s;
    }
    .pub-nav.scrolled {
        box-shadow: 0 4px 24px rgba(15,23,42,0.10);
        background: rgba(255,255,255,0.98);
    }
    .pub-nav-inner {
        max-width: 80rem; margin: 0 auto;
        padding: 0 1.5rem;
        display: flex; align-items: center; justify-content: space-between;
        height: 68px;
    }
    .pub-logo {
        display: flex; align-items: center; gap: 0.75rem;
        text-decoration: none; flex-shrink: 0;
    }
    .pub-logo-img {
        width: 44px; height: 44px; border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 2px 10px rgba(37,99,235,0.25);
        border: 2px solid #bfdbfe;
        transition: transform 0.2s;
    }
    .pub-logo:hover .pub-logo-img { transform: scale(1.07) rotate(3deg); }
    .pub-logo-text { display: none; }
    @media (min-width: 640px) { .pub-logo-text { display: block; } }
    .pub-logo-name { font-size: 0.875rem; font-weight: 800; color: #1e3a8a; line-height: 1.2; letter-spacing: -0.01em; }
    .pub-logo-sub  { font-size: 0.68rem; color: #64748b; font-weight: 500; }
    .pub-links { display: none; align-items: center; gap: 0.125rem; }
    @media (min-width: 768px) { .pub-links { display: flex; } }
    .pub-link {
        display: flex; align-items: center; gap: 5px;
        padding: 0.4rem 0.875rem;
        border-radius: 0.625rem;
        font-size: 0.875rem; font-weight: 500; color: #374151;
        text-decoration: none; transition: all 0.18s;
        white-space: nowrap; position: relative;
    }
    .pub-link::after {
        content: '';
        position: absolute; bottom: -1px; left: 50%; right: 50%;
        height: 2px; background: #2563eb; border-radius: 9999px;
        transition: left 0.2s, right 0.2s;
    }
    .pub-link:hover { color: #2563eb; background: #eff6ff; }
    .pub-link.active { color: #2563eb; font-weight: 700; background: #eff6ff; }
    .pub-link.active::after { left: 12px; right: 12px; }
    .pub-link.live-link { color: #dc2626; }
    .pub-link.live-link:hover { background: #fef2f2; }
    .pub-link.live-link .live-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: #dc2626; animation: livePulse 1.4s infinite;
    }
    @keyframes livePulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(1.4)} }
    .pub-nav-actions { display: flex; align-items: center; gap: 0.5rem; }
    .pub-btn-ghost {
        padding: 0.45rem 1rem; font-size: 0.8125rem; font-weight: 600;
        color: #374151; text-decoration: none; border-radius: 0.625rem; transition: all 0.18s;
    }
    .pub-btn-ghost:hover { background: #f1f5f9; color: #1e3a8a; }
    .pub-btn-solid {
        padding: 0.45rem 1.125rem; font-size: 0.8125rem; font-weight: 700;
        color: #fff; background: linear-gradient(135deg, #1e3a8a, #2563eb);
        border-radius: 0.625rem; text-decoration: none;
        box-shadow: 0 2px 8px rgba(37,99,235,0.35); transition: all 0.18s;
    }
    .pub-btn-solid:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(37,99,235,0.45); }
    .pub-hamburger {
        display: flex; width: 38px; height: 38px; border-radius: 0.5rem;
        align-items: center; justify-content: center;
        background: transparent; border: 1.5px solid #e5e7eb;
        cursor: pointer; transition: all 0.18s;
    }
    .pub-hamburger:hover { background: #f1f5f9; border-color: #c7d2e0; }
    @media (min-width: 768px) { .pub-hamburger { display: none; } }

    /* Mobile drawer */
    .pub-drawer { display: none; position: fixed; inset: 0; z-index: 100; }
    .pub-drawer.open { display: block; }
    .pub-drawer-overlay {
        position: absolute; inset: 0;
        background: rgba(15,23,42,0.45); backdrop-filter: blur(2px);
        animation: fadeIn 0.2s ease;
    }
    .pub-drawer-panel {
        position: absolute; top: 0; right: 0; bottom: 0;
        width: min(320px, 88vw); background: #fff;
        box-shadow: -8px 0 40px rgba(0,0,0,0.18);
        display: flex; flex-direction: column;
        animation: slideIn 0.25s ease; overflow-y: auto;
    }
    @keyframes fadeIn  { from{opacity:0} to{opacity:1} }
    @keyframes slideIn { from{transform:translateX(100%)} to{transform:translateX(0)} }
    .pub-drawer-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; flex-shrink: 0;
    }
    .pub-drawer-close {
        width: 34px; height: 34px; border-radius: 50%;
        background: #f1f5f9; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.15s;
    }
    .pub-drawer-close:hover { background: #e2e8f0; }
    .pub-drawer-nav { padding: 0.75rem; display: flex; flex-direction: column; gap: 2px; flex: 1; }
    .pub-drawer-link {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.75rem 1rem; border-radius: 0.75rem;
        font-size: 0.9375rem; font-weight: 500; color: #374151;
        text-decoration: none; transition: all 0.15s;
    }
    .pub-drawer-link .dlink-icon {
        width: 34px; height: 34px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; background: #f8faff; transition: background 0.15s;
    }
    .pub-drawer-link:hover { background: #eff6ff; color: #2563eb; }
    .pub-drawer-link:hover .dlink-icon { background: #dbeafe; }
    .pub-drawer-link.active { background: #eff6ff; color: #2563eb; font-weight: 700; }
    .pub-drawer-link.active .dlink-icon { background: #dbeafe; }
    .pub-drawer-footer {
        padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;
        display: flex; gap: 0.625rem; flex-shrink: 0;
    }

    /* Footer */
    .pub-footer {
        background: linear-gradient(160deg, #0f172a 0%, #1e3a8a 60%, #1e40af 100%);
        color: #e2e8f0; position: relative; overflow: hidden;
    }
    .pub-footer::before {
        content: ''; position: absolute; top: -120px; right: -120px;
        width: 400px; height: 400px;
        background: radial-gradient(circle, rgba(96,165,250,0.1), transparent 65%);
        pointer-events: none;
    }

    /* Chatbot */
    @media (max-width: 639px) {
        #chatbot-widget { bottom: 1rem; right: 1rem; }
        #chatbot-panel { width: calc(100vw - 2rem) !important; right: 0; max-width: 360px; }
    }

    /* Responsive font */
    @media (max-width: 479px) {
        .text-4xl { font-size: 1.875rem !important; line-height: 1.2 !important; }
        .text-3xl { font-size: 1.5rem !important; }
        .text-2xl { font-size: 1.25rem !important; }
        .text-xl  { font-size: 1.1rem !important; }
    }
    @media (max-width: 767px) {
        .grid.grid-cols-1.md\:grid-cols-3 { grid-template-columns: 1fr !important; }
        div[style*="minmax(160px"] { grid-template-columns: repeat(2, 1fr) !important; }
        div[style*="minmax(300px"] { grid-template-columns: 1fr !important; }
        div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
    }
    .mass-grid { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)) !important; }
    @media (max-width: 639px) {
        div[style*="repeat(auto-fit,minmax(200px"] { grid-template-columns: 1fr !important; }
    }
    </style>
</head>
<body class="font-sans antialiased bg-white text-gray-800">

    {{-- NAVIGATION --}}
    <nav class="pub-nav" id="pub-nav">
        <div class="pub-nav-inner">
            <a href="{{ route('home') }}" class="pub-logo">
                <img src="{{ asset('images/parish-logo.png') }}" alt="MHC Parish" class="pub-logo-img">
                <div class="pub-logo-text">
                    <p class="pub-logo-name">Mary Help of Christians</p>
                    <p class="pub-logo-sub">Niugan, Cabuyao, Laguna</p>
                </div>
            </a>

            <div class="pub-links">
                @foreach([
                    ['home',          'Home'],
                    ['about',         'About'],
                    ['services',      'Services'],
                    ['announcements', 'Announcements'],
                    ['events',        'Events'],
                    ['gallery',       'Gallery'],
                    ['contact',       'Contact'],
                ] as [$r, $l])
                <a href="{{ route($r) }}"
                   class="pub-link {{ request()->routeIs($r.'*') ? 'active' : '' }}">{{ $l }}</a>
                @endforeach
                <a href="{{ route('livestream') }}"
                   class="pub-link live-link {{ request()->routeIs('livestream') ? 'active' : '' }}">
                    <span class="live-dot"></span> Live
                </a>
            </div>

            <div class="pub-nav-actions">
                @auth
                    @if(auth()->user()->hasRole(['super_admin','parish_secretary','finance_officer']))
                        <a href="{{ route('admin.dashboard') }}" class="pub-btn-solid hidden sm:inline-flex">Admin Panel</a>
                    @else
                        <a href="{{ route('parishioner.dashboard') }}" class="pub-btn-solid hidden sm:inline-flex">My Portal</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="pub-btn-ghost hidden sm:inline-flex">Login</a>
                    <a href="{{ route('register') }}" class="pub-btn-solid hidden sm:inline-flex">Register</a>
                @endauth
                <button class="pub-hamburger" id="pub-hamburger" aria-label="Open menu">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    {{-- MOBILE DRAWER --}}
    <div class="pub-drawer" id="pub-drawer">
        <div class="pub-drawer-overlay" id="pub-drawer-overlay"></div>
        <div class="pub-drawer-panel">
            <div class="pub-drawer-head">
                <div class="pub-logo">
                    <img src="{{ asset('images/parish-logo.png') }}" alt="MHC Parish"
                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #bfdbfe;">
                    <div>
                        <p style="font-size:0.8rem;font-weight:800;color:#1e3a8a;margin:0;line-height:1.2;">MHC Parish</p>
                        <p style="font-size:0.65rem;color:#64748b;margin:0;">Niugan, Cabuyao</p>
                    </div>
                </div>
                <button class="pub-drawer-close" id="pub-drawer-close">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <nav class="pub-drawer-nav">
                @foreach([
                    ['home',          'Home',          'home',          'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['about',         'About',         'about',         'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                    ['services',      'Services',      'services*',     'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['announcements', 'Announcements', 'announcements*','M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                    ['events',        'Events',        'events*',       'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['gallery',       'Gallery',       'gallery',       'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['livestream',    'Live Stream',   'livestream',    'M15 10l4.553-2.169A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
                    ['contact',       'Contact Us',    'contact',       'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ] as [$route, $label, $match, $svgPath])
                <a href="{{ route($route) }}"
                   class="pub-drawer-link {{ request()->routeIs($match) ? 'active' : '' }}">
                    <span class="dlink-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $svgPath }}"/>
                        </svg>
                    </span>
                    {{ $label }}
                    @if($route === 'livestream')
                    <span style="width:6px;height:6px;border-radius:50%;background:#dc2626;animation:livePulse 1.4s infinite;margin-left:auto;flex-shrink:0;"></span>
                    @endif
                </a>
                @endforeach
            </nav>

            <div class="pub-drawer-footer">
                @auth
                    @if(auth()->user()->hasRole(['super_admin','parish_secretary','finance_officer']))
                        <a href="{{ route('admin.dashboard') }}"
                           style="flex:1;padding:.625rem 1rem;background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;font-weight:700;font-size:.875rem;border-radius:.75rem;text-align:center;text-decoration:none;">
                            Admin Panel
                        </a>
                    @else
                        <a href="{{ route('parishioner.dashboard') }}"
                           style="flex:1;padding:.625rem 1rem;background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;font-weight:700;font-size:.875rem;border-radius:.75rem;text-align:center;text-decoration:none;">
                            My Portal
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       style="flex:1;padding:.625rem 1rem;border:1.5px solid #e5e7eb;color:#374151;font-weight:600;font-size:.875rem;border-radius:.75rem;text-align:center;text-decoration:none;background:#fff;">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                       style="flex:1;padding:.625rem 1rem;background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;font-weight:700;font-size:.875rem;border-radius:.75rem;text-align:center;text-decoration:none;">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div style="background:#f0fdf4;border-bottom:1px solid #86efac;color:#166534;padding:0.75rem 1.5rem;text-align:center;font-size:0.875rem;font-weight:500;">
        &#10003; {{ session('success') }}
    </div>
    @endif

    <main>@yield('content')</main>

    {{-- FOOTER --}}
    <footer class="pub-footer mt-16">
        {{-- Wave separator --}}
        <div style="overflow:hidden;line-height:0;margin-bottom:-1px;">
            <svg viewBox="0 0 1440 60" preserveAspectRatio="none" style="width:100%;height:40px;display:block;" fill="#ffffff">
                <path d="M0,40 C360,0 1080,80 1440,20 L1440,0 L0,0 Z"/>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10">

                {{-- Brand --}}
                <div>
                    <div style="display:flex;align-items:center;gap:0.875rem;margin-bottom:1.25rem;">
                        <img src="{{ asset('images/parish-logo.png') }}" alt="Logo"
                             style="width:52px;height:52px;border-radius:50%;object-fit:cover;
                                    border:2px solid rgba(147,197,253,0.5);
                                    box-shadow:0 4px 12px rgba(0,0,0,0.25);">
                        <div>
                            <p style="font-weight:800;font-size:0.9375rem;color:#fff;margin:0;line-height:1.25;">
                                Mary Help of Christians Parish
                            </p>
                            <p style="font-size:0.75rem;color:#93c5fd;margin:0;">Diocese of San Pablo</p>
                        </div>
                    </div>
                    <p style="color:#94a3b8;font-size:0.875rem;line-height:1.75;margin-bottom:1.5rem;">
                        Serving the community of Southville 1, Niugan, Cabuyao, Laguna with faith, hope, and love.
                        <em style="color:#bfdbfe;">Feast Day: May 24</em>
                    </p>

                    {{-- Social icons --}}
                    @php $socials = \App\Models\Setting::socials(); @endphp
                    <div style="display:flex;gap:0.625rem;flex-wrap:wrap;">
                        @foreach([
                            ['facebook',  'Facebook',  '#1877f2', 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
                            ['messenger', 'Messenger', '#0084ff', 'M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.652V24l4.088-2.242c1.092.3 2.246.464 3.443.464C18.627 22.222 24 17.248 24 11.111 24 4.974 18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26 6.559-6.963 3.129 3.26 5.889-3.26-6.559 6.963z'],
                            ['instagram', 'Instagram', '#e1306c', 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z'],
                            ['youtube',   'YouTube',   '#ff0000', 'M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z'],
                            ['tiktok',    'TikTok',    '#333',    'M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z'],
                        ] as [$key, $lbl, $color, $svgPath])
                        @if($socials[$key])
                        <a href="{{ $socials[$key] }}" target="_blank" rel="noopener noreferrer"
                           aria-label="{{ $lbl }}"
                           style="width:38px;height:38px;border-radius:50%;
                                  background:rgba(255,255,255,0.08);
                                  border:1px solid rgba(255,255,255,0.12);
                                  display:flex;align-items:center;justify-content:center;
                                  transition:all 0.2s;text-decoration:none;"
                           onmouseover="this.style.background='{{ $color }}';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 14px rgba(0,0,0,0.3)';"
                           onmouseout="this.style.background='rgba(255,255,255,0.08)';this.style.transform='';this.style.boxShadow='';">
                            <svg width="16" height="16" fill="white" viewBox="0 0 24 24">
                                <path d="{{ $svgPath }}"/>
                            </svg>
                        </a>
                        @endif
                        @endforeach
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <p style="font-weight:700;font-size:0.875rem;color:#fff;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:1.25rem;">Quick Links</p>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.625rem;">
                        @foreach([
                            ['home',         'Home'],
                            ['about',        'About the Parish'],
                            ['services',     'Services &amp; Sacraments'],
                            ['announcements','Announcements'],
                            ['events',       'Events'],
                            ['gallery',      'Gallery'],
                            ['livestream',   'Live Stream'],
                            ['contact',      'Contact Us'],
                        ] as [$route, $label])
                        <li>
                            <a href="{{ route($route) }}"
                               style="display:flex;align-items:center;gap:8px;color:#94a3b8;font-size:0.875rem;text-decoration:none;transition:color 0.15s;"
                               onmouseover="this.style.color='#fff';"
                               onmouseout="this.style.color='#94a3b8';">
                                <svg style="width:12px;height:12px;flex-shrink:0;transition:transform 0.15s;"
                                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                                {!! $label !!}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <p style="font-weight:700;font-size:0.875rem;color:#fff;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:1.25rem;">Contact</p>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.875rem;">
                        @foreach([
                            ['M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z', config('parish.address')],
                            ['M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', config('parish.phone')],
                            ['M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', config('parish.email')],
                            ['M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'Tue&ndash;Sun: 9AM&ndash;12NN, 2PM&ndash;5PM'],
                            ['M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', config('parish.priest').' &mdash; Parish Priest'],
                        ] as [$svgPath, $text])
                        <li style="display:flex;align-items:flex-start;gap:0.75rem;">
                            <svg style="width:16px;height:16px;color:#60a5fa;flex-shrink:0;margin-top:2px;"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $svgPath }}"/>
                            </svg>
                            <span style="color:#94a3b8;font-size:0.8375rem;line-height:1.6;">{!! $text !!}</span>
                        </li>
                        @endforeach
                    </ul>
                    <div style="margin-top:1.5rem;">
                        <a href="{{ route('contact') }}"
                           style="display:inline-flex;align-items:center;gap:8px;
                                  background:rgba(255,255,255,0.1);color:#fff;font-weight:600;
                                  font-size:0.8125rem;padding:0.625rem 1.25rem;border-radius:0.625rem;
                                  text-decoration:none;border:1px solid rgba(255,255,255,0.15);
                                  transition:all 0.2s;"
                           onmouseover="this.style.background='rgba(255,255,255,0.2)';"
                           onmouseout="this.style.background='rgba(255,255,255,0.1)';">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Send a Message
                        </a>
                    </div>
                </div>

            </div>

            {{-- Bottom bar --}}
            <div style="border-top:1px solid rgba(255,255,255,0.08);padding-top:1.5rem;
                        display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;">
                <p style="font-size:0.8125rem;color:#475569;margin:0;">
                    &copy; {{ date('Y') }} Mary Help of Christians Parish. All rights reserved.
                </p>
                <div style="display:flex;gap:1.25rem;">
                    <a href="{{ route('walkin.index') }}"
                       style="font-size:0.8125rem;color:#475569;text-decoration:none;transition:color 0.15s;"
                       onmouseover="this.style.color='#93c5fd';" onmouseout="this.style.color='#475569';">Walk-in Booking</a>
                    <a href="{{ route('register') }}"
                       style="font-size:0.8125rem;color:#475569;text-decoration:none;transition:color 0.15s;"
                       onmouseover="this.style.color='#93c5fd';" onmouseout="this.style.color='#475569';">Register</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- CHATBOT --}}
    <div id="chatbot-widget" class="fixed bottom-6 right-6 z-50">
        <button id="chatbot-toggle"
                style="width:56px;height:56px;background:linear-gradient(135deg,#1e3a8a,#2563eb);
                       color:#fff;border-radius:50%;box-shadow:0 4px 20px rgba(37,99,235,0.45);
                       display:flex;align-items:center;justify-content:center;
                       border:none;cursor:pointer;transition:all 0.2s;"
                onmouseover="this.style.transform='scale(1.08)';"
                onmouseout="this.style.transform='';">
            <svg id="chat-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div id="chatbot-panel"
             class="hidden absolute bottom-16 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
             style="height:450px;">
            <div style="background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;padding:0.875rem 1.125rem;display:flex;align-items:center;gap:0.75rem;">
                <div style="width:34px;height:34px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                    </svg>
                </div>
                <div>
                    <p style="font-weight:700;font-size:0.875rem;margin:0;">Parish Assistant</p>
                    <p style="font-size:0.7rem;color:#bfdbfe;margin:0;">Always here to help</p>
                </div>
            </div>

            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
                <div class="bot-message">
                    <div class="bg-white rounded-2xl rounded-tl-none px-3 py-2 text-sm shadow-sm max-w-xs">
                        Hello! Welcome to Mary Help of Christians Parish. How can I help you today?
                    </div>
                </div>
            </div>

            <div class="p-3 border-t bg-white">
                <div class="flex gap-2">
                    <input id="chat-input" type="text" placeholder="Type a message..."
                           class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:border-blue-500"
                           style="background:#f8faff;">
                    <button id="chat-send"
                            style="width:36px;height:36px;background:linear-gradient(135deg,#1e3a8a,#2563eb);
                                   color:#fff;border-radius:50%;display:flex;align-items:center;
                                   justify-content:center;border:none;cursor:pointer;flex-shrink:0;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </div>
                <button id="chat-escalate" class="mt-2 w-full text-xs text-blue-600 hover:underline">
                    Talk to parish staff
                </button>
            </div>
        </div>
    </div>

    <script>
        // Navbar scroll shadow
        const nav = document.getElementById('pub-nav');
        window.addEventListener('scroll', () => {
            nav.classList.toggle('scrolled', window.scrollY > 20);
        }, { passive: true });

        // Mobile drawer
        const drawer        = document.getElementById('pub-drawer');
        const drawerOverlay = document.getElementById('pub-drawer-overlay');
        const drawerClose   = document.getElementById('pub-drawer-close');
        const hamburger     = document.getElementById('pub-hamburger');

        function openDrawer()  { drawer.classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeDrawer() { drawer.classList.remove('open'); document.body.style.overflow = ''; }

        hamburger?.addEventListener('click', openDrawer);
        drawerClose?.addEventListener('click', closeDrawer);
        drawerOverlay?.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', e => e.key === 'Escape' && closeDrawer());

        // Chatbot
        const sessionId = 'chat_' + Math.random().toString(36).substr(2, 9);
        const toggle    = document.getElementById('chatbot-toggle');
        const panel     = document.getElementById('chatbot-panel');
        const chatIcon  = document.getElementById('chat-icon');
        const closeIcon = document.getElementById('close-icon');
        const messages  = document.getElementById('chat-messages');
        const input     = document.getElementById('chat-input');
        const sendBtn   = document.getElementById('chat-send');
        const escalate  = document.getElementById('chat-escalate');

        toggle.addEventListener('click', () => {
            panel.classList.toggle('hidden');
            chatIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });

        function addMessage(text, sender) {
            const div    = document.createElement('div');
            div.className = sender === 'user' ? 'flex justify-end' : 'bot-message';
            const bubble  = document.createElement('div');
            bubble.className = sender === 'user'
                ? 'bg-blue-700 text-white rounded-2xl rounded-tr-none px-3 py-2 text-sm max-w-xs'
                : 'bg-white rounded-2xl rounded-tl-none px-3 py-2 text-sm shadow-sm max-w-xs';
            bubble.innerHTML = text.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            div.appendChild(bubble);
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        async function sendMessage() {
            const text = input.value.trim();
            if (!text) return;
            input.value = '';
            addMessage(text, 'user');
            try {
                const res  = await fetch('{{ route("chatbot.chat") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ message: text, session_id: sessionId }),
                });
                const data = await res.json();
                addMessage(data.message, 'bot');
            } catch { addMessage('Sorry, I encountered an error. Please try again.', 'bot'); }
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', e => e.key === 'Enter' && sendMessage());

        escalate.addEventListener('click', async () => {
            try {
                const res  = await fetch('{{ route("chatbot.escalate") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ session_id: sessionId, message: input.value || 'User requested staff assistance' }),
                });
                const data = await res.json();
                addMessage(data.message, 'bot');
            } catch { addMessage('Unable to connect to staff. Please call our office directly.', 'bot'); }
        });
    </script>

    @stack('scripts')
</body>
</html>
