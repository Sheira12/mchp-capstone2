<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'My Portal') — {{ config('parish.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
    /* ── Reset & Base ── */
    *, *::before, *::after { box-sizing: border-box; }
    body { margin: 0; background: #f0f4f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

    /* ── Layout shell ── */
    .portal-shell { display: flex; min-height: 100vh; }

    /* ── Sidebar ── */
    .portal-sidebar {
        width: 260px;
        flex-shrink: 0;
        background: #1e3a8a;
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0; left: 0; bottom: 0;
        z-index: 100;
        overflow-y: auto;
        transition: transform 0.3s ease;
    }
    .portal-sidebar::-webkit-scrollbar { width: 4px; }
    .portal-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }

    /* Sidebar brand */
    .sb-brand {
        display: flex; align-items: center; gap: 12px;
        padding: 1.25rem 1.25rem 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        text-decoration: none;
    }
    .sb-brand img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.3); }
    .sb-brand-text p:first-child { font-size: 0.875rem; font-weight: 700; color: #fff; margin: 0; line-height: 1.2; }
    .sb-brand-text p:last-child  { font-size: 0.7rem; color: #93c5fd; margin: 0; }

    /* User card in sidebar */
    .sb-user {
        display: flex; align-items: center; gap: 10px;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .sb-avatar {
        width: 42px; height: 42px; border-radius: 50%;
        object-fit: cover; flex-shrink: 0;
        border: 2px solid rgba(255,255,255,0.3);
    }
    .sb-avatar-placeholder {
        width: 42px; height: 42px; border-radius: 50%;
        background: linear-gradient(135deg,#3b82f6,#6366f1);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.125rem; font-weight: 700; color: #fff;
        flex-shrink: 0; border: 2px solid rgba(255,255,255,0.3);
    }
    .sb-user-info { flex: 1; min-width: 0; }
    .sb-user-name { font-size: 0.8125rem; font-weight: 700; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }
    .sb-user-role { font-size: 0.7rem; color: #93c5fd; margin: 1px 0 0; }
    .sb-verified { display: inline-flex; align-items: center; gap: 3px; background: rgba(74,222,128,0.2); border: 1px solid rgba(74,222,128,0.3); color: #86efac; font-size: 0.62rem; font-weight: 700; padding: 1px 7px; border-radius: 9999px; margin-top: 4px; }
    .sb-incomplete { display: inline-flex; align-items: center; gap: 3px; background: rgba(251,191,36,0.2); border: 1px solid rgba(251,191,36,0.3); color: #fde68a; font-size: 0.62rem; font-weight: 700; padding: 1px 7px; border-radius: 9999px; margin-top: 4px; text-decoration: none; }

    /* Nav section label */
    .sb-section { font-size: 0.62rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: rgba(147,197,253,0.6); padding: 1rem 1.25rem 0.25rem; }

    /* Nav item */
    .sb-nav { padding: 0.25rem 0.75rem; }
    .sb-link {
        display: flex; align-items: center; gap: 10px;
        padding: 0.625rem 0.75rem; border-radius: 0.625rem;
        font-size: 0.875rem; font-weight: 500; color: rgba(255,255,255,0.75);
        text-decoration: none; transition: all 0.18s;
        position: relative;
    }
    .sb-link:hover { background: rgba(255,255,255,0.1); color: #fff; }
    .sb-link.active { background: rgba(255,255,255,0.15); color: #fff; font-weight: 700; }
    .sb-link svg { width: 18px; height: 18px; flex-shrink: 0; opacity: 0.8; }
    .sb-link.active svg { opacity: 1; }
    .sb-link:hover svg { opacity: 1; }
    .sb-badge { margin-left: auto; background: #f59e0b; color: #fff; font-size: 0.65rem; font-weight: 700; padding: 1px 7px; border-radius: 9999px; }

    /* Sidebar footer */
    .sb-footer { margin-top: auto; padding: 0.75rem; border-top: 1px solid rgba(255,255,255,0.1); }
    .sb-logout {
        display: flex; align-items: center; gap: 10px;
        width: 100%; padding: 0.625rem 0.75rem; border-radius: 0.625rem;
        font-size: 0.875rem; font-weight: 500; color: rgba(252,165,165,0.9);
        background: transparent; border: none; cursor: pointer;
        transition: all 0.18s; text-align: left;
    }
    .sb-logout:hover { background: rgba(239,68,68,0.15); color: #fca5a5; }
    .sb-logout svg { width: 18px; height: 18px; flex-shrink: 0; }

    /* ── Main content area ── */
    .portal-main { flex: 1; margin-left: 260px; display: flex; flex-direction: column; min-height: 100vh; }

    /* ── Top bar ── */
    .portal-topbar {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        height: 60px;
        display: flex; align-items: center; justify-content: space-between;
        padding: 0 1.5rem;
        position: sticky; top: 0; z-index: 50;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .topbar-left { display: flex; align-items: center; gap: 12px; }
    .topbar-title { font-size: 1rem; font-weight: 700; color: #0f172a; }
    .topbar-right { display: flex; align-items: center; gap: 12px; }
    .topbar-website {
        display: flex; align-items: center; gap: 6px;
        font-size: 0.8125rem; font-weight: 500; color: #64748b;
        text-decoration: none; padding: 0.375rem 0.75rem;
        border-radius: 0.5rem; transition: all 0.15s;
    }
    .topbar-website:hover { background: #f1f5f9; color: #2563eb; }
    .topbar-website svg { width: 15px; height: 15px; }

    /* Hamburger (mobile) */
    .sb-toggle {
        display: none; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 0.5rem;
        background: transparent; border: none; cursor: pointer;
        color: #64748b; transition: background 0.15s;
    }
    .sb-toggle:hover { background: #f1f5f9; }
    .sb-toggle svg { width: 20px; height: 20px; }

    /* ── Page content ── */
    .portal-content { flex: 1; padding: 1.5rem; }

    /* ── Flash messages ── */
    .flash { display: flex; align-items: flex-start; gap: 12px; padding: 0.875rem 1.125rem; border-radius: 0.875rem; margin-bottom: 1.25rem; font-size: 0.875rem; }
    .flash-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .flash-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
    .flash-error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .flash svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }

    /* ── Badges ── */
    .badge { display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.02em; }
    .badge-pending   { background: #fef3c7; color: #92400e; }
    .badge-confirmed { background: #d1fae5; color: #065f46; }
    .badge-completed { background: #dbeafe; color: #1e40af; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }
    .badge-paid      { background: #d1fae5; color: #065f46; }
    .badge-failed    { background: #fee2e2; color: #991b1b; }
    .badge-draft     { background: #f1f5f9; color: #475569; }
    .badge-issued    { background: #dbeafe; color: #1e40af; }
    .badge-released  { background: #d1fae5; color: #065f46; }

    /* ── Mobile overlay ── */
    .sb-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; }

    /* ── Responsive ── */
    @media (max-width: 1023px) {
        .portal-sidebar { transform: translateX(-100%); }
        .portal-sidebar.open { transform: translateX(0); }
        .portal-main { margin-left: 0; }
        .sb-toggle { display: flex; }
        .sb-overlay.open { display: block; }
    }
    </style>
</head>
<body>

{{-- Mobile overlay --}}
<div class="sb-overlay" id="sb-overlay" onclick="closeSidebar()"></div>

<div class="portal-shell">

    {{-- ═══════════════ SIDEBAR ═══════════════ --}}
    <aside class="portal-sidebar" id="portal-sidebar">

        {{-- Brand --}}
        <a href="{{ route('home') }}" class="sb-brand">
            <img src="{{ asset('images/parish-logo.png') }}" alt="MHC Parish">
            <div class="sb-brand-text">
                <p>MHC Parish</p>
                <p>Parishioner Portal</p>
            </div>
        </a>

        {{-- User card --}}
        <div class="sb-user">
            @if(auth()->user()->parishioner?->photo_path)
                <img src="{{ Storage::url(auth()->user()->parishioner->photo_path) }}" class="sb-avatar" alt="Photo">
            @else
                <div class="sb-avatar-placeholder">{{ substr(auth()->user()->name, 0, 1) }}</div>
            @endif
            <div class="sb-user-info">
                <p class="sb-user-name">{{ auth()->user()->name }}</p>
                <p class="sb-user-role">Parishioner</p>
                @if(auth()->user()->parishioner)
                    <span class="sb-verified">✓ Verified</span>
                @else
                    <a href="{{ route('parishioner.profile') }}" class="sb-incomplete">⚠ Complete Profile</a>
                @endif
            </div>
        </div>

        {{-- Navigation --}}
        <div class="sb-nav">
            <div class="sb-section">Main</div>

            <a href="{{ route('parishioner.dashboard') }}"
               class="sb-link {{ request()->routeIs('parishioner.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('parishioner.profile') }}"
               class="sb-link {{ request()->routeIs('parishioner.profile') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                My Profile
            </a>
        </div>

        <div class="sb-nav">
            <div class="sb-section">Services</div>

            <a href="{{ route('parishioner.bookings.index') }}"
               class="sb-link {{ request()->routeIs('parishioner.bookings.index') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                My Bookings
                @php $pendingCount = auth()->user()->parishioner?->bookings()->where('status','pending')->count() ?? 0; @endphp
                @if($pendingCount > 0)
                    <span class="sb-badge">{{ $pendingCount }}</span>
                @endif
            </a>

            <a href="{{ route('parishioner.bookings.create') }}"
               class="sb-link {{ request()->routeIs('parishioner.bookings.create') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Book a Service
            </a>
        </div>

        <div class="sb-nav">
            <div class="sb-section">Records</div>

            <a href="{{ route('parishioner.certificates.index') }}"
               class="sb-link {{ request()->routeIs('parishioner.certificates.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                My Certificates
            </a>

            <a href="{{ route('parishioner.payments.index') }}"
               class="sb-link {{ request()->routeIs('parishioner.payments.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Payments
            </a>
        </div>

        <div class="sb-nav">
            <div class="sb-section">Parish</div>

            <a href="{{ route('announcements') }}" class="sb-link">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                Announcements
            </a>

            <a href="{{ route('services') }}" class="sb-link">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Parish Services
            </a>

            <a href="{{ route('contact') }}" class="sb-link">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Contact Parish
            </a>
        </div>

        {{-- Logout --}}
        <div class="sb-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-logout">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══════════════ MAIN ═══════════════ --}}
    <div class="portal-main">

        {{-- Top bar --}}
        <header class="portal-topbar">
            <div class="topbar-left">
                <button class="sb-toggle" onclick="openSidebar()" aria-label="Open menu">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <span class="topbar-title">@yield('title', 'Dashboard')</span>
            </div>
            <div class="topbar-right">
                <a href="{{ route('home') }}" class="topbar-website">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Parish Website
                </a>
                {{-- User avatar --}}
                <a href="{{ route('parishioner.profile') }}"
                   style="display:flex;align-items:center;gap:8px;text-decoration:none;padding:4px 10px 4px 4px;border-radius:9999px;border:1.5px solid #e2e8f0;transition:background 0.15s;"
                   onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='transparent';">
                    @if(auth()->user()->parishioner?->photo_path)
                        <img src="{{ Storage::url(auth()->user()->parishioner->photo_path) }}"
                             style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                    @else
                        <div style="width:30px;height:30px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;font-size:0.875rem;font-weight:700;color:#fff;">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                    @endif
                    <span style="font-size:0.8125rem;font-weight:600;color:#374151;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ auth()->user()->name }}
                    </span>
                </a>
            </div>
        </header>

        {{-- Page content --}}
        <main class="portal-content">

            {{-- Flash messages --}}
            @if(session('success'))
            <div class="flash flash-success">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @if(session('info'))
            <div class="flash flash-info">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <span>{{ session('info') }}</span>
            </div>
            @endif
            @if($errors->any())
            <div class="flash flash-error">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <ul style="margin:0;padding:0;list-style:none;">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
function openSidebar() {
    document.getElementById('portal-sidebar').classList.add('open');
    document.getElementById('sb-overlay').classList.add('open');
}
function closeSidebar() {
    document.getElementById('portal-sidebar').classList.remove('open');
    document.getElementById('sb-overlay').classList.remove('open');
}
</script>

@stack('scripts')
</body>
</html>
