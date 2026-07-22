<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') — {{ config('parish.name') }}</title>
    <meta name="description" content="@yield('meta-description', 'Mary Help of Christians Parish - Southville 1, Niugan, Cabuyao, Laguna')">
    <link rel="icon" type="image/png" href="{{ asset('images/parish-logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
    /* ── Public layout responsive fixes ── */
    * { box-sizing: border-box; }
    img { max-width: 100%; height: auto; }

    /* Chatbot: move up on mobile so it doesn't cover bottom nav */
    @media (max-width: 639px) {
        #chatbot-widget { bottom: 1rem; right: 1rem; }
        #chatbot-panel { width: calc(100vw - 2rem) !important; right: 0; max-width: 360px; }
    }

    /* Font scaling on very small screens */
    @media (max-width: 479px) {
        .text-4xl { font-size: 1.875rem !important; line-height: 1.2 !important; }
        .text-3xl { font-size: 1.5rem !important; }
        .text-2xl { font-size: 1.25rem !important; }
        .text-xl  { font-size: 1.1rem !important; }
    }

    /* Home page: CTA grid stack on mobile */
    @media (max-width: 767px) {
        .grid.grid-cols-1.md\:grid-cols-3 { grid-template-columns: 1fr !important; }
        /* Services grid */
        div[style*="minmax(160px"] { grid-template-columns: repeat(2, 1fr) !important; }
        /* Announcement grid */
        div[style*="minmax(300px"] { grid-template-columns: 1fr !important; }
        /* CTA 2-col */
        div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
    }

    /* Mass schedule grid: wrap nicely */
    .mass-grid { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)) !important; }

    /* Contact strip: stack on mobile */
    @media (max-width: 639px) {
        div[style*="repeat(auto-fit,minmax(200px"] { grid-template-columns: 1fr !important; }
    }

    /* Footer grid */
    @media (max-width: 767px) {
        .grid.grid-cols-1.md\:grid-cols-3 { grid-template-columns: 1fr !important; }
    }

    /* Prevent horizontal scroll */
    body { overflow-x: hidden; }
    section, .max-w-7xl { max-width: 100%; }
    </style>
</head>
<body class="font-sans antialiased bg-white text-gray-800">

    {{-- Navigation --}}
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo" class="w-10 h-10 rounded-full object-cover">
                    <div class="hidden sm:block leading-tight">
                        <p class="text-sm font-bold text-blue-900">Mary Help of Christians Parish</p>
                        <p class="text-xs text-gray-500">Southville 1, Niugan, Cabuyao, Laguna</p>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-6">
                    <a href="{{ route('home') }}" class="nav-public {{ request()->routeIs('home') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Home</a>
                    <a href="{{ route('about') }}" class="nav-public {{ request()->routeIs('about') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">About</a>
                    <a href="{{ route('services') }}" class="nav-public {{ request()->routeIs('services') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Services</a>
                    <a href="{{ route('announcements') }}" class="nav-public {{ request()->routeIs('announcements*') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Announcements</a>
                    <a href="{{ route('events') }}" class="nav-public {{ request()->routeIs('events*') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Events</a>
                    <a href="{{ route('gallery') }}" class="nav-public {{ request()->routeIs('gallery') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Gallery</a>
                    <a href="{{ route('livestream') }}" class="nav-public {{ request()->routeIs('livestream') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">
                        <span class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>Live
                        </span>
                    </a>
                    <a href="{{ route('contact') }}" class="nav-public {{ request()->routeIs('contact') ? 'text-blue-700 font-semibold' : 'text-gray-600 hover:text-blue-700' }}">Contact</a>
                </div>

                {{-- Auth buttons --}}
                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->hasRole(['super_admin', 'parish_secretary', 'finance_officer']))
                            <a href="{{ route('admin.dashboard') }}" class="btn-primary text-sm">Admin Panel</a>
                        @else
                            <a href="{{ route('parishioner.dashboard') }}" class="btn-primary text-sm">My Portal</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-blue-700">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm">Register</a>
                    @endauth

                    {{-- Mobile menu button --}}
                    <button id="mobile-menu-btn" class="md:hidden text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden md:hidden border-t bg-white">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-700' }}">Home</a>
                <a href="{{ route('about') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('about') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-700' }}">About</a>
                <a href="{{ route('services') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('services') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-700' }}">Services</a>
                <a href="{{ route('announcements') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('announcements*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-700' }}">Announcements</a>
                <a href="{{ route('events') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('events*') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-700' }}">Events</a>
                <a href="{{ route('gallery') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('gallery') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-700' }}">📷 Gallery</a>
                <a href="{{ route('livestream') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('livestream') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-700' }}">🔴 Livestream</a>
                <a href="{{ route('contact') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('contact') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-700' }}">Contact</a>
                <div class="pt-2 border-t border-gray-100 mt-2">
                    @auth
                        @if(auth()->user()->hasRole(['super_admin', 'parish_secretary', 'finance_officer']))
                            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-700 text-center">Admin Panel</a>
                        @else
                            <a href="{{ route('parishioner.dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-700 text-center">My Portal</a>
                        @endif
                    @else
                        <div class="flex gap-2">
                            <a href="{{ route('login') }}" class="flex-1 px-3 py-2.5 rounded-lg text-sm font-medium text-center border border-gray-200 text-gray-700 hover:bg-gray-50">Login</a>
                            <a href="{{ route('register') }}" class="flex-1 px-3 py-2.5 rounded-lg text-sm font-semibold text-center text-white bg-blue-700">Register</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border-b border-green-200 text-green-800 px-4 py-3 text-sm text-center">
            {{ session('success') }}
        </div>
    @endif

    {{-- Page content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-blue-900 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/parish-logo.png') }}" alt="Logo" class="w-12 h-12 rounded-full object-cover">
                        <div>
                            <p class="font-bold">Mary Help of Christians Parish</p>
                            <p class="text-blue-300 text-sm">Diocese of San Pablo</p>
                        </div>
                    </div>
                    <p class="text-blue-200 text-sm">Serving the community of Southville 1, Niugan, Cabuyao, Laguna with faith, hope, and love.</p>
                </div>
                <div>
                    <h3 class="font-semibold mb-3">Quick Links</h3>
                    <ul class="space-y-2 text-blue-200 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white">About the Parish</a></li>
                        <li><a href="{{ route('services') }}" class="hover:text-white">Services & Sacraments</a></li>
                        <li><a href="{{ route('announcements') }}" class="hover:text-white">Announcements</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-3">Contact Information</h3>
                    <ul class="space-y-2 text-blue-200 text-sm">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Southville 1, Niugan, Cabuyao, Laguna
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ config('parish.phone') }}
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ config('parish.email') }}
                        </li>
                    </ul>
                    <div class="mt-4">
                        <p class="text-sm font-medium mb-1">Office Hours</p>
                        <p class="text-blue-200 text-sm">Mon–Fri: 8AM–5PM</p>
                        <p class="text-blue-200 text-sm">Sat: 8AM–12PM</p>
                    </div>

                    {{-- Social Media — dynamic links from Settings --}}
                    @php $socials = \App\Models\Setting::socials(); @endphp
                    <div class="mt-5">
                        <p class="text-sm font-semibold text-white mb-3">Follow Us</p>
                        <div class="flex items-center gap-3 flex-wrap">
                            {{-- Facebook --}}
                            @if($socials['facebook'])
                            <a href="{{ $socials['facebook'] }}" target="_blank" rel="noopener noreferrer"
                               title="Facebook" aria-label="Facebook"
                               class="w-9 h-9 rounded-full bg-blue-800 hover:bg-[#1877f2] flex items-center justify-center transition-all duration-200 hover:scale-110 group">
                                <svg class="w-4 h-4 text-blue-200 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            @endif
                            {{-- Messenger --}}
                            @if($socials['messenger'])
                            <a href="{{ $socials['messenger'] }}" target="_blank" rel="noopener noreferrer"
                               title="Messenger" aria-label="Messenger"
                               class="w-9 h-9 rounded-full bg-blue-800 hover:bg-[#0084ff] flex items-center justify-center transition-all duration-200 hover:scale-110 group">
                                <svg class="w-4 h-4 text-blue-200 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.652V24l4.088-2.242c1.092.3 2.246.464 3.443.464C18.627 22.222 24 17.248 24 11.111 24 4.974 18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26 6.559-6.963 3.129 3.26 5.889-3.26-6.559 6.963z"/>
                                </svg>
                            </a>
                            @endif
                            {{-- Instagram --}}
                            @if($socials['instagram'])
                            <a href="{{ $socials['instagram'] }}" target="_blank" rel="noopener noreferrer"
                               title="Instagram" aria-label="Instagram"
                               class="w-9 h-9 rounded-full bg-blue-800 hover:bg-[#e1306c] flex items-center justify-center transition-all duration-200 hover:scale-110 group">
                                <svg class="w-4 h-4 text-blue-200 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                            @endif
                            {{-- YouTube --}}
                            @if($socials['youtube'])
                            <a href="{{ $socials['youtube'] }}" target="_blank" rel="noopener noreferrer"
                               title="YouTube" aria-label="YouTube"
                               class="w-9 h-9 rounded-full bg-blue-800 hover:bg-[#ff0000] flex items-center justify-center transition-all duration-200 hover:scale-110 group">
                                <svg class="w-4 h-4 text-blue-200 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                            @endif
                            {{-- TikTok --}}
                            @if($socials['tiktok'])
                            <a href="{{ $socials['tiktok'] }}" target="_blank" rel="noopener noreferrer"
                               title="TikTok" aria-label="TikTok"
                               class="w-9 h-9 rounded-full bg-blue-800 hover:bg-gray-900 flex items-center justify-center transition-all duration-200 hover:scale-110 group">
                                <svg class="w-4 h-4 text-blue-200 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                </svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-blue-800 mt-8 pt-6 text-center text-blue-300 text-sm">
                © {{ date('Y') }} Mary Help of Christians Parish. All rights reserved.
            </div>
        </div>
    </footer>

    {{-- Chatbot Widget --}}
    <div id="chatbot-widget" class="fixed bottom-6 right-6 z-50">
        <button id="chatbot-toggle" class="w-14 h-14 bg-blue-700 hover:bg-blue-800 text-white rounded-full shadow-lg flex items-center justify-center transition-all">
            <svg id="chat-icon" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <svg id="close-icon" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div id="chatbot-panel" class="hidden absolute bottom-16 right-0 w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden" style="height: 450px;">
            <div class="bg-blue-700 text-white px-4 py-3 flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-sm">Parish Assistant</p>
                    <p class="text-xs text-blue-200">Online</p>
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
                    <input id="chat-input" type="text" placeholder="Type a message..." class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none focus:border-blue-500">
                    <button id="chat-send" class="w-9 h-9 bg-blue-700 text-white rounded-full flex items-center justify-center hover:bg-blue-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </div>
                <button id="chat-escalate" class="mt-2 w-full text-xs text-blue-600 hover:underline">Talk to parish staff</button>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

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
            const div = document.createElement('div');
            div.className = sender === 'user' ? 'flex justify-end' : 'bot-message';
            const bubble = document.createElement('div');
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
                const res = await fetch('{{ route("chatbot.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message: text, session_id: sessionId }),
                });
                const data = await res.json();
                addMessage(data.message, 'bot');
            } catch {
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', e => e.key === 'Enter' && sendMessage());

        escalate.addEventListener('click', async () => {
            const lastMsg = input.value || 'User requested staff assistance';
            try {
                const res = await fetch('{{ route("chatbot.escalate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ session_id: sessionId, message: lastMsg }),
                });
                const data = await res.json();
                addMessage(data.message, 'bot');
            } catch {
                addMessage('Unable to connect to staff. Please call our office directly.', 'bot');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
