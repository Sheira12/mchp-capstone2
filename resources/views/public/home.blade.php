@extends('layouts.public')
@section('title', 'Welcome')

@push('styles')
<style>
.hero-bg {
    position: absolute; inset: 0;
    background-image: url('/images/church-bg.jpg');
    background-size: cover; background-position: center;
    filter: blur(6px) brightness(0.22) saturate(0.5);
    transform: scale(1.08); z-index: 0;
}
.hero-overlay {
    position: absolute; inset: 0; z-index: 1;
    background: linear-gradient(180deg, rgba(5,12,48,0.75) 0%, rgba(5,12,48,0.45) 45%, rgba(5,12,48,0.88) 100%);
}
.hero-content { position: relative; z-index: 2; }
.svc-card {
    background: #fff; border-radius: 1rem; border: 1px solid #f1f5f9;
    padding: 1.5rem 1rem; text-align: center;
    transition: all 0.25s ease; text-decoration: none; display: block;
}
.svc-card:hover { box-shadow: 0 16px 40px rgba(59,130,246,0.13); transform: translateY(-4px); border-color: #bfdbfe; }
.svc-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; }
.ann-card { background: #fff; border-radius: 1rem; border: 1px solid #f1f5f9; overflow: hidden; transition: all 0.25s ease; text-decoration: none; display: flex; flex-direction: column; }
.ann-card:hover { box-shadow: 0 16px 40px rgba(0,0,0,0.10); transform: translateY(-4px); }
.mass-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 1rem; }
.mass-card { background: #fff; border-radius: 1rem; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.mass-card-head { background: #1d4ed8; color: #fff; text-align: center; padding: 0.6rem 0.5rem; font-weight: 700; font-size: 0.8rem; }
.mass-card-body { padding: 0.75rem 0.5rem; }
.mass-time { text-align: center; padding: 0.35rem 0; border-bottom: 1px solid #f8faff; }
.mass-time:last-child { border-bottom: none; }
.mass-time p { font-weight: 700; font-size: 0.85rem; color: #1e3a8a; margin: 0; }
.mass-time span { font-size: 0.7rem; color: #94a3b8; }
</style>
@endpush

@section('content')

{{-- HERO --}}
<section class="relative text-white overflow-hidden" style="min-height:100vh; display:flex; align-items:center; justify-content:center;">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content w-full max-w-3xl mx-auto px-4 py-20 text-center">

        <div class="inline-flex items-center gap-2 mb-7 px-4 py-1.5 rounded-full text-xs font-bold tracking-widest uppercase"
             style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.15);color:#93c5fd;backdrop-filter:blur(8px);">
            <span style="width:6px;height:6px;border-radius:50%;background:#60a5fa;display:inline-block;"></span>
            Diocese of San Pablo
        </div>

        <div style="display:flex;justify-content:center;margin-bottom:1.5rem;">
            <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo"
                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,0.55);box-shadow:0 8px 32px rgba(0,0,0,0.5);">
        </div>

        <h1 style="font-size:clamp(1.9rem,4.5vw,3.4rem);font-weight:800;line-height:1.15;text-shadow:0 4px 24px rgba(0,0,0,0.7);margin-bottom:0.75rem;">
            Mary Help of Christians Parish
        </h1>
        <p style="font-size:1.05rem;color:#bfdbfe;margin-bottom:0.4rem;text-shadow:0 2px 8px rgba(0,0,0,0.5);">
            Southville 1, Niugan, Cabuyao, Laguna
        </p>

        <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin:1.5rem 0;">
            <div style="height:1px;width:56px;background:linear-gradient(to right,transparent,rgba(147,197,253,0.6));"></div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#93c5fd"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg>
            <div style="height:1px;width:56px;background:linear-gradient(to left,transparent,rgba(147,197,253,0.6));"></div>
        </div>

        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-bottom:3rem;">
            <a href="{{ route('services') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e3a8a;font-weight:700;font-size:0.875rem;padding:0.8rem 1.75rem;border-radius:9999px;box-shadow:0 6px 24px rgba(0,0,0,0.35);text-decoration:none;transition:all 0.2s;"
               onmouseover="this.style.background='#eff6ff';this.style.transform='translateY(-2px)';"
               onmouseout="this.style.background='#fff';this.style.transform='';">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Our Services
            </a>
            <a href="{{ route('walkin.index') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:rgba(212,175,55,0.85);color:#fff;font-weight:700;font-size:0.875rem;padding:0.8rem 1.75rem;border-radius:9999px;border:1.5px solid rgba(212,175,55,0.5);box-shadow:0 6px 24px rgba(0,0,0,0.25);text-decoration:none;transition:all 0.2s;"
               onmouseover="this.style.background='rgba(212,175,55,1)';this.style.transform='translateY(-2px)';"
               onmouseout="this.style.background='rgba(212,175,55,0.85)';this.style.transform='';">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Walk-in Booking
            </a>
            <a href="{{ route('register') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:rgba(37,99,235,0.9);color:#fff;font-weight:700;font-size:0.875rem;padding:0.8rem 1.75rem;border-radius:9999px;border:1.5px solid rgba(147,197,253,0.35);box-shadow:0 6px 24px rgba(0,0,0,0.35);text-decoration:none;transition:all 0.2s;"
               onmouseover="this.style.background='#2563eb';this.style.transform='translateY(-2px)';"
               onmouseout="this.style.background='rgba(37,99,235,0.9)';this.style.transform='';">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Register
            </a>
            <a href="{{ route('login') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:transparent;color:#fff;font-weight:600;font-size:0.875rem;padding:0.8rem 1.75rem;border-radius:9999px;border:1.5px solid rgba(255,255,255,0.28);text-decoration:none;transition:all 0.2s;"
               onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.transform='translateY(-2px)';"
               onmouseout="this.style.background='transparent';this.style.transform='';">
                Sign In
            </a>
        </div>

        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:2.5rem;">
            @foreach([['Est. 2015','Parish Founded'],['Daily','Holy Mass'],['Online','Services']] as $i => $s)
            @if($i > 0)<div style="width:1px;background:rgba(255,255,255,0.12);align-self:stretch;" class="hidden sm:block"></div>@endif
            <div style="text-align:center;">
                <p style="font-size:1.4rem;font-weight:800;color:#fff;line-height:1;margin:0;">{{ $s[0] }}</p>
                <p style="font-size:0.62rem;font-weight:600;letter-spacing:0.18em;text-transform:uppercase;color:#93c5fd;margin-top:4px;">{{ $s[1] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div style="position:absolute;bottom:2rem;left:50%;transform:translateX(-50%);z-index:2;animation:bounce 2s infinite;">
        <svg width="22" height="22" fill="none" stroke="rgba(147,197,253,0.75)" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- MASS SCHEDULE --}}
<section style="padding:5rem 0;background:#f8faff;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div style="text-align:center;margin-bottom:3rem;">
            <span class="section-label" style="display:inline-block;font-size:0.7rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#3b82f6;margin-bottom:0.5rem;">Worship With Us</span>
            <h2 style="font-size:2rem;font-weight:800;color:#0f172a;margin:0 0 0.75rem;">Mass Schedule</h2>
            <div style="width:48px;height:3px;background:#2563eb;border-radius:9999px;margin:0 auto;"></div>
        </div>

        @php
            $grouped = $massSchedules->sortBy('day_of_week')->groupBy('day_of_week');
            $days = [0=>'Sunday',1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'];
            $dayShort = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
            $headColors = [0=>'#1d4ed8',1=>'#1e40af',2=>'#1e40af',3=>'#1e40af',4=>'#1e40af',5=>'#1e40af',6=>'#1d4ed8'];
        @endphp

        @if($grouped->count())
        <div class="mass-grid">
            @foreach($grouped as $day => $schedules)
            <div class="mass-card">
                <div class="mass-card-head" style="background:{{ $headColors[$day] ?? '#1d4ed8' }};">
                    {{ $days[$day] ?? 'Special' }}
                </div>
                <div class="mass-card-body">
                    @foreach($schedules->sortBy('time') as $schedule)
                    <div class="mass-time">
                        <p>{{ \Carbon\Carbon::parse($schedule->time)->format('g:i A') }}</p>
                        <span>{{ $schedule->language }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p style="text-align:center;color:#94a3b8;">Mass schedule coming soon.</p>
        @endif

        <div style="text-align:center;margin-top:2rem;">
            <a href="{{ route('contact') }}" style="color:#2563eb;font-weight:600;font-size:0.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                Contact us for Mass intentions
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- SERVICES --}}
<section style="padding:5rem 0;background:#fff;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div style="text-align:center;margin-bottom:3rem;">
            <span style="display:inline-block;font-size:0.7rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#3b82f6;margin-bottom:0.5rem;">What We Offer</span>
            <h2 style="font-size:2rem;font-weight:800;color:#0f172a;margin:0 0 0.75rem;">Parish Services</h2>
            <div style="width:48px;height:3px;background:#2563eb;border-radius:9999px;margin:0 auto 1rem;"></div>
            <p style="color:#64748b;max-width:520px;margin:0 auto;font-size:0.95rem;">From sacraments to blessings, we accompany you in every milestone of your faith journey.</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1.25rem;">
            @php
            $svcs = [
                ['bg'=>'#eff6ff','ic'=>'#2563eb','svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>','name'=>'Baptism','desc'=>'Welcome into the faith'],
                ['bg'=>'#fdf2f8','ic'=>'#db2777','svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>','name'=>'Marriage','desc'=>'Holy Matrimony'],
                ['bg'=>'#f5f3ff','ic'=>'#7c3aed','svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>','name'=>'Confirmation','desc'=>'Strengthen your faith'],
                ['bg'=>'#f0fdf4','ic'=>'#16a34a','svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>','name'=>'First Communion','desc'=>'Receive the Eucharist'],
                ['bg'=>'#fff7ed','ic'=>'#ea580c','svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>','name'=>'House Blessing','desc'=>'Bless your home'],
                ['bg'=>'#fefce8','ic'=>'#ca8a04','svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>','name'=>'Certificates','desc'=>'Official documents'],
                ['bg'=>'#f0f9ff','ic'=>'#0284c7','svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>','name'=>'Mass Intentions','desc'=>'Offer Mass for loved ones'],
                ['bg'=>'#f0fdfa','ic'=>'#0d9488','svg'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>','name'=>'Book Online','desc'=>'Schedule your appointment'],
            ];
            @endphp

            @foreach($svcs as $svc)
            <a href="{{ route('services') }}" class="svc-card">
                <div class="svc-icon" style="background:{{ $svc['bg'] }};">
                    <svg width="24" height="24" fill="none" stroke="{{ $svc['ic'] }}" stroke-width="2" viewBox="0 0 24 24">
                        {!! $svc['svg'] !!}
                    </svg>
                </div>
                <div>
                    <p style="font-weight:700;font-size:0.875rem;color:#0f172a;margin:0 0 4px;">{{ $svc['name'] }}</p>
                    <p style="font-size:0.75rem;color:#94a3b8;margin:0;">{{ $svc['desc'] }}</p>
                </div>
            </a>
            @endforeach
        </div>

        <div style="text-align:center;margin-top:2.5rem;">
            <a href="{{ route('services') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;font-weight:700;font-size:0.875rem;padding:0.875rem 2rem;border-radius:9999px;box-shadow:0 4px 16px rgba(37,99,235,0.35);text-decoration:none;transition:all 0.2s;"
               onmouseover="this.style.background='#1d4ed8';this.style.transform='translateY(-2px)';"
               onmouseout="this.style.background='#2563eb';this.style.transform='';">
                View All Services
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ANNOUNCEMENTS --}}
@if($announcements->count())
<section style="padding:5rem 0;background:#f8faff;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:3rem;flex-wrap:wrap;gap:1rem;">
            <div>
                <span style="display:inline-block;font-size:0.7rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#3b82f6;margin-bottom:0.5rem;">Latest News</span>
                <h2 style="font-size:2rem;font-weight:800;color:#0f172a;margin:0 0 0.75rem;">Announcements</h2>
                <div style="width:48px;height:3px;background:#2563eb;border-radius:9999px;"></div>
            </div>
            <a href="{{ route('announcements') }}" style="color:#2563eb;font-weight:600;font-size:0.875rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">
                View all
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
            @foreach($announcements->take(3) as $announcement)
            <a href="{{ route('announcements.show', $announcement) }}" class="ann-card">
                <div style="position:relative;height:200px;overflow:hidden;">
                    @if($announcement->image_path)
                    <img src="{{ Storage::url($announcement->image_path) }}" alt="{{ $announcement->title }}"
                         style="width:100%;height:100%;object-fit:cover;transition:transform 0.4s ease;"
                         onmouseover="this.style.transform='scale(1.05)';"
                         onmouseout="this.style.transform='';">
                    @else
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#dbeafe,#e0e7ff);display:flex;align-items:center;justify-content:center;">
                        <svg width="48" height="48" fill="none" stroke="#93c5fd" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    </div>
                    @endif
                    <div style="position:absolute;top:12px;left:12px;">
                        <span style="background:#2563eb;color:#fff;font-size:0.65rem;font-weight:700;padding:3px 10px;border-radius:9999px;text-transform:uppercase;letter-spacing:0.08em;">{{ $announcement->category }}</span>
                    </div>
                </div>
                <div style="padding:1.25rem;flex:1;display:flex;flex-direction:column;">
                    <h3 style="font-weight:700;font-size:1rem;color:#0f172a;margin:0 0 0.5rem;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                        {{ $announcement->title }}
                    </h3>
                    <p style="font-size:0.85rem;color:#64748b;flex:1;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin:0 0 1rem;">
                        {{ strip_tags($announcement->content) }}
                    </p>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding-top:0.75rem;border-top:1px solid #f1f5f9;">
                        <span style="font-size:0.75rem;color:#94a3b8;">{{ $announcement->published_at?->format('M d, Y') }}</span>
                        <span style="font-size:0.75rem;font-weight:600;color:#2563eb;">Read more &rarr;</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- GALLERY PREVIEW --}}
@php $galleryItems = \App\Models\GalleryItem::orderBy('sort_order')->orderByDesc('created_at')->take(8)->get(); @endphp
@if($galleryItems->count())
<section class="py-14 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Parish Gallery</h2>
                <p class="text-gray-500 text-sm mt-1">Moments from our parish community</p>
            </div>
            <a href="{{ route('gallery') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                View all photos <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach($galleryItems as $item)
            <a href="{{ route('gallery') }}" class="group aspect-square bg-gray-100 rounded-xl overflow-hidden block">
                <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}"
                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                     loading="lazy">
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- LIVESTREAM PREVIEW --}}
@php $latestVideos = \App\Models\Livestream::active()->orderByDesc('created_at')->take(3)->get(); @endphp
@if($latestVideos->count())
<section class="py-14 bg-gray-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span>
                    Live & On-Demand
                </h2>
                <p class="text-gray-400 text-sm mt-1">Watch Masses and events from anywhere</p>
            </div>
            <a href="{{ route('livestream') }}" class="text-sm font-semibold text-blue-400 hover:text-blue-300 flex items-center gap-1">
                All videos <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($latestVideos as $ls)
            <a href="{{ route('livestream') }}" class="group block rounded-xl overflow-hidden bg-gray-800 hover:ring-2 hover:ring-red-500 transition">
                <div class="relative aspect-video bg-gray-900">
                    <img src="{{ $ls->thumbnail }}" alt="{{ $ls->title }}"
                         class="w-full h-full object-cover opacity-80 group-hover:opacity-60 transition"
                         onerror="this.style.display='none'">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-red-600/80 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                    @if($ls->type === 'live')
                        <span class="absolute top-2 left-2 flex items-center gap-1 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded">
                            <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>LIVE
                        </span>
                    @endif
                </div>
                <div class="p-3">
                    <p class="text-white text-sm font-semibold line-clamp-2">{{ $ls->title }}</p>
                    @if($ls->scheduled_at)
                        <p class="text-gray-400 text-xs mt-1">{{ $ls->scheduled_at->format('M d, Y') }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- CTA BANNER --}}
<section style="padding:5rem 0;background:linear-gradient(135deg,#1e3a8a 0%,#312e81 100%);position:relative;overflow:hidden;">
    <div style="position:absolute;top:-80px;right:-80px;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(96,165,250,0.15),transparent 70%);"></div>
    <div style="position:absolute;bottom:-80px;left:-80px;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(129,140,248,0.15),transparent 70%);"></div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8" style="position:relative;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:center;" class="lg:grid-cols-2">
            <div style="color:#fff;">
                <span style="display:inline-block;font-size:0.7rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#93c5fd;margin-bottom:1rem;">Digital Parish Services</span>
                <h2 style="font-size:clamp(1.6rem,3vw,2.25rem);font-weight:800;line-height:1.2;margin:0 0 1rem;">Manage Your Parish<br>Services Online</h2>
                <p style="color:#bfdbfe;font-size:0.95rem;line-height:1.7;margin:0 0 2rem;">Register as a parishioner to book services, request certificates, pay fees via GCash or Maya, and track your sacramental records — all from home.</p>
                <div style="display:flex;flex-wrap:wrap;gap:12px;">
                    <a href="{{ route('register') }}"
                       style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e3a8a;font-weight:700;font-size:0.875rem;padding:0.8rem 1.75rem;border-radius:9999px;box-shadow:0 4px 20px rgba(0,0,0,0.3);text-decoration:none;transition:all 0.2s;"
                       onmouseover="this.style.background='#eff6ff';this.style.transform='translateY(-2px)';"
                       onmouseout="this.style.background='#fff';this.style.transform='';">
                        Create Account
                    </a>
                    <a href="{{ route('login') }}"
                       style="display:inline-flex;align-items:center;gap:8px;background:transparent;color:#fff;font-weight:600;font-size:0.875rem;padding:0.8rem 1.75rem;border-radius:9999px;border:1.5px solid rgba(255,255,255,0.3);text-decoration:none;transition:all 0.2s;"
                       onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.transform='translateY(-2px)';"
                       onmouseout="this.style.background='transparent';this.style.transform='';">
                        Sign In
                    </a>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                @foreach([['📅','Book Services','Schedule sacraments online'],['📜','Get Certificates','Request parish documents'],['💳','Pay Online','GCash & Maya supported'],['📋','Track Records','View sacramental history']] as $f)
                <div style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);border-radius:1rem;padding:1.25rem;backdrop-filter:blur(8px);transition:all 0.2s;"
                     onmouseover="this.style.background='rgba(255,255,255,0.12)';"
                     onmouseout="this.style.background='rgba(255,255,255,0.07)';">
                    <div style="font-size:1.5rem;margin-bottom:0.5rem;">{{ $f[0] }}</div>
                    <p style="font-weight:700;font-size:0.875rem;color:#fff;margin:0 0 4px;">{{ $f[1] }}</p>
                    <p style="font-size:0.75rem;color:#93c5fd;margin:0;">{{ $f[2] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- CONTACT STRIP --}}
<section style="padding:3.5rem 0;background:#fff;border-top:1px solid #f1f5f9;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem;text-align:center;">
            @foreach([
                ['#eff6ff','#2563eb','M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z','Location','Southville 1, Niugan<br>Cabuyao, Laguna'],
                ['#eff6ff','#2563eb','M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','Office Hours','Tue–Sun: 9AM–12NN<br>2PM–5PM'],
                ['#eff6ff','#2563eb','M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','Contact Us','<a href="{{ route(\'contact\') }}" style="color:#2563eb;font-weight:600;text-decoration:none;">Send us a message &rarr;</a>']
            ] as $c)
            <div style="display:flex;flex-direction:column;align-items:center;gap:0.75rem;">
                <div style="width:48px;height:48px;border-radius:50%;background:{{ $c[0] }};display:flex;align-items:center;justify-content:center;">
                    <svg width="22" height="22" fill="none" stroke="{{ $c[1] }}" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $c[2] }}"/></svg>
                </div>
                <p style="font-weight:700;font-size:0.875rem;color:#0f172a;margin:0;">{{ $c[3] }}</p>
                <p style="font-size:0.85rem;color:#64748b;margin:0;line-height:1.6;">{!! $c[4] !!}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
