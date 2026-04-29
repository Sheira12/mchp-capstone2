{{-- Sidebar content (shared between desktop and mobile) --}}
<div class="p-5">
    {{-- User card --}}
    <div class="flex flex-col items-center text-center pb-5 mb-2 border-b border-gray-100">
        @if(auth()->user()->parishioner?->photo_path)
            <img src="{{ Storage::url(auth()->user()->parishioner->photo_path) }}"
                 class="w-16 h-16 rounded-full object-cover border-2 border-blue-100 shadow mb-3">
        @else
            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow mb-3">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
        @endif
        <p class="font-bold text-sm text-gray-900 leading-tight">{{ auth()->user()->name }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Parishioner</p>
        @if(auth()->user()->parishioner)
        <span class="mt-2 inline-flex items-center gap-1 bg-green-100 text-green-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
            Verified
        </span>
        @else
        <a href="{{ route('parishioner.profile') }}" class="mt-2 inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-xs font-semibold px-2.5 py-0.5 rounded-full hover:bg-amber-200 transition">
            ⚠ Complete Profile
        </a>
        @endif
    </div>

    {{-- Navigation --}}
    <nav class="space-y-0.5">
        <div class="portal-nav-section">Main</div>

        <a href="{{ route('parishioner.dashboard') }}"
           class="portal-nav-item {{ request()->routeIs('parishioner.dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('parishioner.profile') }}"
           class="portal-nav-item {{ request()->routeIs('parishioner.profile') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            My Profile
        </a>

        <div class="portal-nav-section">Services</div>

        <a href="{{ route('parishioner.bookings.index') }}"
           class="portal-nav-item {{ request()->routeIs('parishioner.bookings.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            My Bookings
            @php $pending = auth()->user()->parishioner?->bookings()->where('status','pending')->count() ?? 0; @endphp
            @if($pending > 0)
            <span class="ml-auto bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pending }}</span>
            @endif
        </a>

        <a href="{{ route('parishioner.bookings.create') }}"
           class="portal-nav-item {{ request()->routeIs('parishioner.bookings.create') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Book a Service
        </a>

        <div class="portal-nav-section">Records</div>

        <a href="{{ route('parishioner.certificates.index') }}"
           class="portal-nav-item {{ request()->routeIs('parishioner.certificates.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Certificates
        </a>

        <a href="{{ route('parishioner.payments.index') }}"
           class="portal-nav-item {{ request()->routeIs('parishioner.payments.*') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Payments
        </a>

        <div class="portal-nav-section">Help</div>

        <a href="{{ route('contact') }}" class="portal-nav-item">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Contact Parish
        </a>

        <a href="{{ route('home') }}" class="portal-nav-item">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
            </svg>
            Parish Website
        </a>
    </nav>

    {{-- Logout --}}
    <div class="mt-4 pt-4 border-t border-gray-100">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="portal-nav-item w-full text-red-500 hover:bg-red-50 hover:text-red-600">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign Out
            </button>
        </form>
    </div>
</div>
