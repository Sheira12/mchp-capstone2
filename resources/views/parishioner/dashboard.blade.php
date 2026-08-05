@extends('layouts.portal')
@section('title', 'My Dashboard')

@push('styles')
<style>
:root {
  --gold: #b8860b;
  --gold-light: #fef9e7;
  --blue-parish: #1e3a8a;
  --green-soft: #d1fae5;
}
/* Hero banner */
.hero-banner {
  background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #2563eb 100%);
  border-radius: 1.25rem;
  padding: 1.5rem;
  color: #fff;
  position: relative;
  overflow: hidden;
}
@media(min-width:640px){ .hero-banner { padding: 2rem 2.5rem; } }
.hero-banner::before {
  content: "";
  position: absolute;
  top: -60px; right: -60px;
  width: 220px; height: 220px;
  background: rgba(255,255,255,0.06);
  border-radius: 50%;
}
.hero-banner::after {
  content: "";
  position: absolute;
  bottom: -80px; left: 30%;
  width: 300px; height: 300px;
  background: rgba(255,255,255,0.04);
  border-radius: 50%;
}
/* Stat cards */
.stat-card {
  border-radius: 1.25rem;
  padding: 1.25rem;
  color: #fff;
  position: relative;
  overflow: hidden;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  transition: transform 0.2s, box-shadow 0.2s;
}
@media(min-width:640px){ .stat-card { padding: 1.5rem; } }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 14px 32px rgba(0,0,0,0.18); }
.stat-card .blob {
  position: absolute; top: -40%; right: -20%;
  width: 160px; height: 160px;
  background: rgba(255,255,255,0.12);
  border-radius: 50%;
}
.stat-card .blob2 {
  position: absolute; bottom: -50%; left: -10%;
  width: 120px; height: 120px;
  background: rgba(255,255,255,0.07);
  border-radius: 50%;
}
/* Quick action cards */
.qa-card {
  background: #fff;
  border-radius: 1.25rem;
  border: 1.5px solid #f1f5f9;
  padding: 1rem 0.75rem;
  text-align: center;
  transition: all 0.25s ease;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.625rem;
  cursor: pointer;
}
@media(min-width:640px){ .qa-card { padding: 1.5rem 1rem; gap: 0.875rem; } }
.qa-card:hover {
  box-shadow: 0 16px 40px rgba(37,99,235,0.14);
  transform: translateY(-5px);
  border-color: #bfdbfe;
}
.qa-icon {
  width: 60px; height: 60px;
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  transition: transform 0.2s;
}
.qa-card:hover .qa-icon { transform: scale(1.1); }
/* Section card */
.section-card {
  background: #fff;
  border-radius: 1.25rem;
  border: 1px solid #f1f5f9;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  overflow: hidden;
}
.section-header {
  padding: 1.125rem 1.5rem;
  border-bottom: 1px solid #f8faff;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
/* Status badges */
.status-pill {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 3px 10px; border-radius: 9999px;
  font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
}
.status-pending  { background: #fef3c7; color: #92400e; }
.status-confirmed{ background: #d1fae5; color: #065f46; }
.status-completed{ background: #dbeafe; color: #1e40af; }
.status-cancelled{ background: #fee2e2; color: #991b1b; }
.status-paid     { background: #d1fae5; color: #065f46; }
.status-failed   { background: #fee2e2; color: #991b1b; }
/* Progress bar */
.progress-bar { height: 6px; border-radius: 9999px; background: #e2e8f0; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 9999px; transition: width 0.6s ease; }
/* Announcement card */
.ann-item {
  display: flex; gap: 0.875rem; padding: 0.875rem 1.5rem;
  border-bottom: 1px solid #f8faff; transition: background 0.15s;
}
.ann-item:last-child { border-bottom: none; }
.ann-item:hover { background: #f8faff; }
/* Mass schedule pill */
.mass-pill {
  display: flex; flex-direction: column; align-items: center;
  background: #eff6ff; border-radius: 0.875rem;
  padding: 0.75rem 1rem; min-width: 90px;
  border: 1px solid #bfdbfe;
}
/* Upcoming booking row */
.upcoming-row {
  display: flex; align-items: center; gap: 1rem;
  padding: 0.875rem 1.5rem;
  border-bottom: 1px solid #f8faff;
  transition: background 0.15s;
}
.upcoming-row:last-child { border-bottom: none; }
.upcoming-row:hover { background: #f8faff; }
/* Notification dot */
@keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:0.4} }
.pulse-dot { animation: pulse-dot 2s infinite; }
/* Responsive grid */
@media(max-width:640px){
  .hero-banner { padding: 1.5rem; }
  .hero-banner h1 { font-size: 1.4rem; }
}
</style>
@endpush

@section('content')
@php
  $user = auth()->user();
  $p    = $parishioner;
  $hour = now()->hour;
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
  $days = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
@endphp
<div class="space-y-6 pb-8">


{{-- ═══ HERO BANNER ═══ --}}
<div class="hero-banner">
    <div class="relative z-10" style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">

        {{-- LEFT: Avatar + Greeting --}}
        <div style="display:flex;align-items:center;gap:1.25rem;flex:1;min-width:0;">
            {{-- Avatar --}}
            <div style="position:relative;flex-shrink:0;">
                @if($p?->photo_path)
                    <img src="{{ Storage::url($p->photo_path) }}"
                         style="width:72px;height:72px;border-radius:1rem;object-fit:cover;border:2px solid rgba(255,255,255,0.5);box-shadow:0 4px 16px rgba(0,0,0,0.25);">
                @else
                    <div style="width:72px;height:72px;border-radius:1rem;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:800;color:#fff;box-shadow:0 4px 16px rgba(0,0,0,0.2);border:2px solid rgba(255,255,255,0.3);">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                @endif
                @if($p)
                <span style="position:absolute;bottom:-3px;right:-3px;width:14px;height:14px;background:#4ade80;border:2px solid #fff;border-radius:50%;" class="pulse-dot"></span>
                @endif
            </div>

            {{-- Text --}}
            <div style="min-width:0;">
                <p style="color:rgba(191,219,254,0.9);font-size:0.875rem;font-weight:500;margin-bottom:2px;">{{ $greeting }},</p>
                <h1 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:800;color:#fff;line-height:1.2;margin:0;">
                    {{ $p?->first_name ?? $user->name }}!
                </h1>
                <div style="margin-top:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    @if($p)
                    <span style="display:inline-flex;align-items:center;gap:4px;background:rgba(74,222,128,0.2);border:1px solid rgba(74,222,128,0.4);color:#bbf7d0;font-size:0.7rem;font-weight:700;padding:2px 10px;border-radius:9999px;letter-spacing:0.05em;">
                        <svg style="width:10px;height:10px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        VERIFIED PARISHIONER
                    </span>
                    @else
                    <span style="display:inline-flex;align-items:center;gap:4px;background:rgba(251,191,36,0.2);border:1px solid rgba(251,191,36,0.4);color:#fde68a;font-size:0.7rem;font-weight:700;padding:2px 10px;border-radius:9999px;">
                        ⚠ PROFILE INCOMPLETE
                    </span>
                    @endif
                    <span style="color:rgba(191,219,254,0.7);font-size:0.75rem;">{{ now()->format('l, F j, Y') }}</span>
                </div>
            </div>
        </div>

        {{-- RIGHT: Actions --}}
        <div style="display:flex;align-items:center;gap:0.625rem;flex-wrap:wrap;">
            <a href="{{ route('parishioner.bookings.create') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e3a8a;font-weight:800;font-size:0.8125rem;padding:0.625rem 1.25rem;border-radius:0.875rem;box-shadow:0 4px 16px rgba(0,0,0,0.2);text-decoration:none;transition:all 0.2s;white-space:nowrap;"
               onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.25)';"
               onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(0,0,0,0.2)';">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Book a Service
            </a>
            <a href="{{ route('parishioner.profile') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);border:1.5px solid rgba(255,255,255,0.25);color:#fff;font-weight:700;font-size:0.875rem;padding:0.75rem 1.25rem;border-radius:0.875rem;text-decoration:none;transition:all 0.2s;white-space:nowrap;"
               onmouseover="this.style.background='rgba(255,255,255,0.2)';"
               onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                My Profile
            </a>
        </div>
    </div>
</div>

{{-- ═══ PROFILE ALERT ═══ --}}
@if(!$p)
<div class="bg-amber-50 border-l-4 border-amber-500 rounded-2xl p-5 flex items-start gap-4 shadow-sm">
    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <div class="flex-1">
        <h3 class="font-bold text-amber-900 mb-1">Complete Your Profile to Get Started</h3>
        <p class="text-sm text-amber-800 mb-3">You need to complete your parishioner profile before you can book services, request certificates, or make payments.</p>
        <a href="{{ route('parishioner.profile') }}"
           class="inline-flex items-center gap-2 bg-amber-600 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-amber-700 shadow-md transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Complete My Profile
        </a>
    </div>
</div>
@endif

{{-- ═══ STAT CARDS ═══ --}}
@if($p)
<div id="stat-grid" style="display:grid;grid-template-columns:repeat(2,1fr);gap:0.75rem;">
    {{-- Total Bookings --}}
    <div class="stat-card" style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%);">
        <div class="blob"></div><div class="blob2"></div>
        <div style="position:relative;z-index:1;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg style="width:20px;height:20px;" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <p style="color:rgba(191,219,254,0.85);font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Total Bookings</p>
            <p style="font-size:2.5rem;font-weight:800;color:#fff;line-height:1.1;margin:4px 0;">{{ $stats['total_bookings'] }}</p>
            <p style="color:rgba(191,219,254,0.7);font-size:0.75rem;">{{ $stats['confirmed_bookings'] }} confirmed</p>
        </div>
    </div>
    {{-- Pending --}}
    <div class="stat-card" style="background:linear-gradient(135deg,#b45309 0%,#f59e0b 100%);">
        <div class="blob"></div><div class="blob2"></div>
        <div style="position:relative;z-index:1;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg style="width:20px;height:20px;" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p style="color:rgba(254,243,199,0.85);font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Pending</p>
            <p style="font-size:2.5rem;font-weight:800;color:#fff;line-height:1.1;margin:4px 0;">{{ $stats['pending_bookings'] }}</p>
            <p style="color:rgba(254,243,199,0.7);font-size:0.75rem;">Awaiting approval</p>
        </div>
    </div>
    {{-- Certificates --}}
    <div class="stat-card" style="background:linear-gradient(135deg,#6d28d9 0%,#a855f7 100%);">
        <div class="blob"></div><div class="blob2"></div>
        <div style="position:relative;z-index:1;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg style="width:20px;height:20px;" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p style="color:rgba(233,213,255,0.85);font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Certificates</p>
            <p style="font-size:2.5rem;font-weight:800;color:#fff;line-height:1.1;margin:4px 0;">{{ $stats['total_certificates'] }}</p>
            <p style="color:rgba(233,213,255,0.7);font-size:0.75rem;">Issued documents</p>
        </div>
    </div>
    {{-- Payments --}}
    <div class="stat-card" style="background:linear-gradient(135deg,#065f46 0%,#10b981 100%);">
        <div class="blob"></div><div class="blob2"></div>
        <div style="position:relative;z-index:1;">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;margin-bottom:12px;">
                <svg style="width:20px;height:20px;" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p style="color:rgba(209,250,229,0.85);font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">Total Paid</p>
            <p style="font-size:2rem;font-weight:800;color:#fff;line-height:1.1;margin:4px 0;">₱{{ number_format($stats['total_paid_amount'], 0) }}</p>
            <p style="color:rgba(209,250,229,0.7);font-size:0.75rem;">{{ $stats['paid_payments'] }} transactions</p>
        </div>
    </div>
</div>
@endif

{{-- ═══ QUICK ACTIONS ═══ --}}
<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-extrabold text-gray-900">Quick Actions</h2>
        <p class="text-xs text-gray-400">What would you like to do today?</p>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('parishioner.bookings.create') }}" class="qa-card">
            <div class="qa-icon" style="background:linear-gradient(135deg,#dbeafe,#eff6ff);">
                <svg class="w-7 h-7" fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-extrabold text-sm text-gray-900">Book a Service</p>
                <p class="text-xs text-gray-400 mt-0.5">Baptism, Wedding, Blessings</p>
            </div>
        </a>
        <a href="{{ route('parishioner.certificates.index') }}" class="qa-card">
            <div class="qa-icon" style="background:linear-gradient(135deg,#fef3c7,#fffbeb);">
                <svg class="w-7 h-7" fill="none" stroke="#b45309" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="font-extrabold text-sm text-gray-900">Certificates</p>
                <p class="text-xs text-gray-400 mt-0.5">Request & download PDFs</p>
            </div>
        </a>
        <a href="{{ route('parishioner.payments.index') }}" class="qa-card">
            <div class="qa-icon" style="background:linear-gradient(135deg,#d1fae5,#ecfdf5);">
                <svg class="w-7 h-7" fill="none" stroke="#065f46" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <p class="font-extrabold text-sm text-gray-900">Make Payment</p>
                <p class="text-xs text-gray-400 mt-0.5">GCash, Maya, Cash</p>
            </div>
        </a>
        <a href="{{ route('parishioner.profile') }}" class="qa-card">
            <div class="qa-icon" style="background:linear-gradient(135deg,#ede9fe,#f5f3ff);">
                <svg class="w-7 h-7" fill="none" stroke="#6d28d9" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="font-extrabold text-sm text-gray-900">My Profile</p>
                <p class="text-xs text-gray-400 mt-0.5">Update personal info</p>
            </div>
        </a>
    </div>
</div>

{{-- ═══ UPCOMING BOOKINGS + RECENT ACTIVITY ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Upcoming Bookings --}}
    <div class="lg:col-span-2 section-card">
        <div class="section-header" style="background:linear-gradient(to right,#eff6ff,#f0f9ff);">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="font-extrabold text-gray-900 text-sm">My Bookings</h2>
            </div>
            <a href="{{ route('parishioner.bookings.index') }}" class="text-xs font-bold text-blue-600 hover:underline flex items-center gap-1">
                View all
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @forelse($recentBookings as $booking)
        @php
            $sc = ['pending'=>'status-pending','confirmed'=>'status-confirmed','completed'=>'status-completed','cancelled'=>'status-cancelled'][$booking->status] ?? 'status-pending';
            $barColor = ['pending'=>'#f59e0b','confirmed'=>'#10b981','completed'=>'#3b82f6','cancelled'=>'#ef4444'][$booking->status] ?? '#94a3b8';
        @endphp
        <div class="upcoming-row">
            {{-- Date block --}}
            <div class="flex-shrink-0 w-12 h-12 rounded-xl flex flex-col items-center justify-center text-center"
                 style="background:#eff6ff;">
                <p class="text-xs font-bold text-blue-600 leading-none">{{ $booking->scheduled_date->format('M') }}</p>
                <p class="text-lg font-extrabold text-blue-900 leading-none">{{ $booking->scheduled_date->format('d') }}</p>
            </div>
            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm text-gray-900 truncate">{{ $booking->getTypeLabel() }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                    @if($booking->scheduled_time)
                    <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') }}</span>
                    @endif
                    @if($booking->service_fee > 0)
                    <span class="text-xs text-gray-400">· ₱{{ number_format($booking->service_fee, 0) }}</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $booking->reference_number }}</p>
            </div>
            {{-- Status + action --}}
            <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                <span class="status-pill {{ $sc }}">{{ $booking->getStatusLabel() }}</span>
                <a href="{{ route('parishioner.bookings.show', $booking) }}"
                   class="text-xs text-blue-600 hover:underline font-semibold">Details →</a>
            </div>
        </div>
        @empty
        <div class="py-12 text-center">
            <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8 text-blue-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-600 mb-1">No bookings yet</p>
            <p class="text-xs text-gray-400 mb-4">Book a parish service to get started</p>
            <a href="{{ route('parishioner.bookings.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-blue-700 shadow-md transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Book Now
            </a>
        </div>
        @endforelse
    </div>

    {{-- Right column: Payments + Certificates --}}
    <div class="space-y-5">

        {{-- Recent Payments --}}
        <div class="section-card">
            <div class="section-header" style="background:linear-gradient(to right,#f0fdf4,#ecfdf5);">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-green-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <h2 class="font-extrabold text-gray-900 text-sm">Payments</h2>
                </div>
                <a href="{{ route('parishioner.payments.index') }}" class="text-xs font-bold text-green-600 hover:underline">View all →</a>
            </div>
            @forelse($recentPayments as $payment)
            @php $ps = ['paid'=>'status-paid','pending'=>'status-pending','failed'=>'status-failed'][$payment->status] ?? 'status-pending'; @endphp
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition">
                <div>
                    <p class="font-bold text-sm text-gray-900">₱{{ number_format($payment->amount, 2) }}</p>
                    <p class="text-xs text-gray-400 capitalize mt-0.5">{{ $payment->payment_method }} · {{ $payment->created_at->format('M d') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="status-pill {{ $ps }}">{{ ucfirst($payment->status) }}</span>
                    @if($payment->status === 'paid')
                    <a href="{{ route('parishioner.payments.receipt', $payment) }}"
                       style="font-size:0.7rem;font-weight:700;color:#2563eb;text-decoration:none;"
                       onmouseover="this.style.textDecoration='underline';"
                       onmouseout="this.style.textDecoration='none';">Receipt</a>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-8 text-center">
                <p class="text-sm text-gray-400">No payments yet</p>
            </div>
            @endforelse
        </div>

        {{-- Certificates --}}
        <div class="section-card">
            <div class="section-header" style="background:linear-gradient(to right,#faf5ff,#f5f3ff);">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h2 class="font-extrabold text-gray-900 text-sm">Certificates</h2>
                </div>
                <a href="{{ route('parishioner.certificates.index') }}" class="text-xs font-bold text-purple-600 hover:underline">View all →</a>
            </div>
            @forelse($certificates as $cert)
            @php $cs = ['draft'=>'status-pending','issued'=>'status-confirmed','released'=>'status-paid'][$cert->status] ?? 'status-pending'; @endphp
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm text-gray-900 capitalize truncate">{{ str_replace('_',' ',$cert->type) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $cert->issued_date->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="status-pill {{ $cs }}">{{ ucfirst($cert->status) }}</span>
                    @if($cert->status === 'released' && $cert->file_path)
                    <a href="{{ route('parishioner.certificates.download', $cert) }}"
                       class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center hover:bg-purple-200 transition" title="Download">
                        <svg class="w-3.5 h-3.5 text-purple-700" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-8 text-center">
                <p class="text-sm text-gray-400">No certificates yet</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ═══ UPCOMING APPOINTMENTS + MASS SCHEDULE ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Upcoming Appointments --}}
    <div class="section-card">
        <div class="section-header" style="background:linear-gradient(to right,#fff7ed,#fffbeb);">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="font-extrabold text-gray-900 text-sm">Upcoming (30 days)</h2>
            </div>
        </div>
        @forelse($upcomingBookings as $booking)
        @php
            $daysLeft = now()->diffInDays($booking->scheduled_date, false);
            $urgency = $daysLeft <= 3 ? 'text-red-600' : ($daysLeft <= 7 ? 'text-amber-600' : 'text-gray-500');
        @endphp
        <div class="upcoming-row">
            <div class="flex-shrink-0 w-12 h-12 rounded-xl flex flex-col items-center justify-center"
                 style="background:{{ $daysLeft <= 3 ? '#fee2e2' : ($daysLeft <= 7 ? '#fef3c7' : '#eff6ff') }};">
                <p class="text-xs font-bold leading-none" style="color:{{ $daysLeft <= 3 ? '#dc2626' : ($daysLeft <= 7 ? '#d97706' : '#2563eb') }}">{{ $booking->scheduled_date->format('M') }}</p>
                <p class="text-lg font-extrabold leading-none" style="color:{{ $daysLeft <= 3 ? '#991b1b' : ($daysLeft <= 7 ? '#92400e' : '#1e3a8a') }}">{{ $booking->scheduled_date->format('d') }}</p>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm text-gray-900 truncate">{{ $booking->getTypeLabel() }}</p>
                <p class="text-xs {{ $urgency }} font-semibold mt-0.5">
                    @if($daysLeft === 0) Today!
                    @elseif($daysLeft === 1) Tomorrow
                    @else In {{ $daysLeft }} days
                    @endif
                </p>
            </div>
            <span class="status-pill {{ ['pending'=>'status-pending','confirmed'=>'status-confirmed'][$booking->status] ?? 'status-pending' }}">
                {{ $booking->getStatusLabel() }}
            </span>
        </div>
        @empty
        <div class="py-10 text-center">
            <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-amber-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm text-gray-500">No upcoming appointments</p>
        </div>
        @endforelse
    </div>

    {{-- Mass Schedule --}}
    <div class="section-card">
        <div class="section-header" style="background:linear-gradient(to right,#f0fdf4,#ecfdf5);">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-green-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="font-extrabold text-gray-900 text-sm">Mass Schedule</h2>
            </div>
            <a href="{{ route('services') }}" class="text-xs font-bold text-green-600 hover:underline">Full schedule →</a>
        </div>
        <div class="p-4">
            @php
                $grouped = $massSchedules->groupBy('day_of_week');
                $dayNames = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
                $today = now()->dayOfWeek;
            @endphp
            @if($grouped->count())
            <div class="flex flex-wrap gap-2">
                @foreach($grouped as $day => $schedules)
                <div class="mass-pill {{ $day == $today ? 'border-green-500 bg-green-50' : '' }}">
                    <p class="text-xs font-extrabold {{ $day == $today ? 'text-green-700' : 'text-blue-700' }}">{{ $dayNames[$day] ?? '?' }}</p>
                    @foreach($schedules->sortBy('time') as $s)
                    <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ \Carbon\Carbon::parse($s->time)->format('g:i A') }}</p>
                    @endforeach
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-4">Mass schedule not available</p>
            @endif
        </div>
    </div>
</div>

{{-- ═══ ANNOUNCEMENTS + HELP ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Announcements --}}
    <div class="lg:col-span-2 section-card">
        <div class="section-header" style="background:linear-gradient(to right,#faf5ff,#f5f3ff);">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <h2 class="font-extrabold text-gray-900 text-sm">Parish Announcements</h2>
            </div>
            <a href="{{ route('announcements') }}" class="text-xs font-bold text-indigo-600 hover:underline">View all →</a>
        </div>
        @forelse($announcements as $ann)
        <a href="{{ route('announcements.show', $ann) }}" class="ann-item group">
            <div class="w-10 h-10 rounded-xl flex-shrink-0 overflow-hidden">
                @if($ann->image_path)
                    <img src="{{ Storage::url($ann->image_path) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm text-gray-900 group-hover:text-indigo-700 transition truncate">{{ $ann->title }}</p>
                <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ strip_tags($ann->content) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $ann->published_at?->diffForHumans() }}</p>
            </div>
            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 flex-shrink-0 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        @empty
        <div class="py-10 text-center">
            <p class="text-sm text-gray-400">No announcements at this time</p>
        </div>
        @endforelse
    </div>

    {{-- Help & Support --}}
    <div class="space-y-4">
        {{-- Help card --}}
        <div class="rounded-2xl p-5 text-white shadow-lg" style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%);">
            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="font-extrabold text-base mb-1">Need Help?</h3>
            <p class="text-blue-200 text-xs mb-4 leading-relaxed">Have questions about parish services? Our staff is ready to assist you.</p>
            <a href="{{ route('contact') }}"
               class="block text-center bg-white text-blue-900 font-bold text-sm py-2.5 rounded-xl hover:bg-blue-50 transition shadow">
                Contact Parish Office
            </a>
        </div>

        {{-- Office Hours --}}
        <div class="section-card p-5">
            <h3 class="font-extrabold text-gray-900 text-sm mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Office Hours
            </h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Tue – Sun</span>
                    <span class="font-semibold text-gray-800">9AM – 12NN</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Afternoon</span>
                    <span class="font-semibold text-gray-800">2PM – 5PM</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Monday</span>
                    <span class="font-semibold text-red-500">Closed</span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100">
                <p class="text-xs text-gray-500">📞 {{ config('parish.phone') }}</p>
                <p class="text-xs text-gray-500 mt-1">✉ {{ config('parish.email') }}</p>
            </div>
        </div>
    </div>
</div>

</div>{{-- end space-y-6 --}}
@endsection

@push('scripts')
<script>
// Make stat grid 4 columns on large screens
function updateStatGrid() {
    const grid = document.getElementById('stat-grid');
    if (!grid) return;
    grid.style.gridTemplateColumns = window.innerWidth >= 1024 ? 'repeat(4,1fr)' : 'repeat(2,1fr)';
}
updateStatGrid();
window.addEventListener('resize', updateStatGrid);

// Auto-dismiss flash messages
setTimeout(() => {
    document.querySelectorAll('[data-dismiss]').forEach(el => {
        el.style.transition = 'opacity 0.5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    });
}, 4000);
</script>
@endpush
