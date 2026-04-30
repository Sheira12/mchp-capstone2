@extends('layouts.public')

@section('title', 'Services & Sacraments')
@section('meta-description', 'Parish services including sacraments, blessings, seminars, and certificates at Mary Help of Christians Parish')

@push('styles')
<style>
/* Sidebar nav */
.svc-sidebar-link {
    display: flex; align-items: center; gap: 10px;
    padding: 0.625rem 1rem; border-radius: 0.625rem;
    font-size: 0.875rem; font-weight: 500; color: #475569;
    text-decoration: none; transition: all 0.2s; border: none;
    background: transparent; cursor: pointer; width: 100%;
}
.svc-sidebar-link:hover { background: #eff6ff; color: #2563eb; }
.svc-sidebar-link.active { background: #eff6ff; color: #2563eb; font-weight: 700; }
.svc-sidebar-link .dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #cbd5e1; flex-shrink: 0; transition: background 0.2s;
}
.svc-sidebar-link.active .dot,
.svc-sidebar-link:hover .dot { background: #2563eb; }

/* Service card */
.svc-card {
    background: #fff;
    border-radius: 1rem;
    border: 1px solid #e8edf5;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.25s ease;
}
.svc-card:hover {
    box-shadow: 0 8px 24px rgba(37,99,235,0.10);
    transform: translateY(-2px);
    border-color: #bfdbfe;
}

/* Category section */
.cat-section { scroll-margin-top: 90px; }

/* Sticky sidebar */
.svc-sidebar-wrap { position: sticky; top: 90px; }

/* Mobile category tabs */
.cat-tab {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 0.5rem 1rem; border-radius: 9999px;
    font-size: 0.8rem; font-weight: 600; white-space: nowrap;
    text-decoration: none; transition: all 0.2s;
    background: #fff; color: #475569; border: 1.5px solid #e2e8f0;
}
.cat-tab:hover, .cat-tab.active { background: #2563eb; color: #fff; border-color: #2563eb; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<section style="background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 100%);padding:3.5rem 0;color:#fff;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 style="font-size:clamp(1.75rem,4vw,2.75rem);font-weight:800;margin-bottom:0.75rem;">Parish Services & Sacraments</h1>
        <p style="font-size:1.05rem;color:#bfdbfe;max-width:560px;margin:0 auto;">
            We are here to accompany you in every milestone of your faith journey.
        </p>
        @guest
        <div style="margin-top:1.5rem;">
            <a href="{{ route('register') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e3a8a;font-weight:700;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:9999px;text-decoration:none;box-shadow:0 4px 16px rgba(0,0,0,0.2);transition:all 0.2s;"
               onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#fff';">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Register to Book a Service
            </a>
        </div>
        @endguest
    </div>
</section>

{{-- Mobile category tabs --}}
<div class="md:hidden bg-white border-b border-gray-100 shadow-sm sticky top-16 z-30 overflow-x-auto">
    <div class="flex gap-2 px-4 py-3" style="min-width:max-content;">
        @foreach($services as $category => $categoryServices)
        <a href="#{{ Str::slug($category) }}" class="cat-tab">
            {{ $category }}
        </a>
        @endforeach
    </div>
</div>

{{-- Main layout: sidebar + content --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex gap-8 items-start">

        {{-- ═══ SIDEBAR ═══ --}}
        <aside class="hidden md:block" style="width:240px;flex-shrink:0;">
            <div class="svc-sidebar-wrap">
                <div style="background:#fff;border-radius:1rem;border:1px solid #e8edf5;box-shadow:0 2px 8px rgba(0,0,0,0.05);overflow:hidden;">

                    {{-- Sidebar header --}}
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;background:linear-gradient(to right,#eff6ff,#f0f4ff);">
                        <p style="font-size:0.7rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:#64748b;margin-bottom:2px;">Browse</p>
                        <p style="font-size:0.9375rem;font-weight:800;color:#0f172a;">Service Categories</p>
                    </div>

                    {{-- Category links --}}
                    <nav style="padding:0.75rem;">
                        @foreach($services as $category => $categoryServices)
                        <a href="#{{ Str::slug($category) }}" class="svc-sidebar-link" data-section="{{ Str::slug($category) }}">
                            <span class="dot"></span>
                            <span style="flex:1;">{{ $category }}</span>
                            <span style="font-size:0.7rem;font-weight:700;background:#f1f5f9;color:#64748b;padding:1px 7px;border-radius:9999px;">
                                {{ $categoryServices->count() }}
                            </span>
                        </a>
                        @endforeach
                    </nav>

                    {{-- CTA in sidebar --}}
                    <div style="padding:1rem 1.25rem;border-top:1px solid #f1f5f9;background:#f8faff;">
                        @auth
                        <a href="{{ route('parishioner.bookings.create') }}"
                           style="display:flex;align-items:center;justify-content:center;gap:6px;background:#2563eb;color:#fff;font-weight:700;font-size:0.8125rem;padding:0.625rem 1rem;border-radius:0.625rem;text-decoration:none;transition:background 0.2s;"
                           onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Book a Service
                        </a>
                        @else
                        <a href="{{ route('register') }}"
                           style="display:flex;align-items:center;justify-content:center;gap:6px;background:#2563eb;color:#fff;font-weight:700;font-size:0.8125rem;padding:0.625rem 1rem;border-radius:0.625rem;text-decoration:none;transition:background 0.2s;"
                           onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                            Register to Book
                        </a>
                        @endauth
                        <a href="{{ route('contact') }}"
                           style="display:flex;align-items:center;justify-content:center;gap:6px;background:#fff;color:#374151;font-weight:600;font-size:0.8125rem;padding:0.625rem 1rem;border-radius:0.625rem;text-decoration:none;border:1.5px solid #e2e8f0;margin-top:0.5rem;transition:background 0.2s;"
                           onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='#fff';">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Contact Parish
                        </a>
                    </div>
                </div>

                {{-- Office hours box --}}
                <div style="margin-top:1rem;background:#fff;border-radius:1rem;border:1px solid #e8edf5;padding:1.125rem;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <p style="font-size:0.8rem;font-weight:700;color:#0f172a;margin-bottom:0.75rem;display:flex;align-items:center;gap:6px;">
                        <svg style="width:14px;height:14px;color:#2563eb;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Office Hours
                    </p>
                    <div style="font-size:0.8rem;color:#475569;line-height:1.8;">
                        <div style="display:flex;justify-content:space-between;"><span>Tue – Sun</span><span style="font-weight:600;color:#0f172a;">9AM – 12NN</span></div>
                        <div style="display:flex;justify-content:space-between;"><span>Afternoon</span><span style="font-weight:600;color:#0f172a;">2PM – 5PM</span></div>
                        <div style="display:flex;justify-content:space-between;"><span>Monday</span><span style="font-weight:600;color:#ef4444;">Closed</span></div>
                    </div>
                    <div style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid #f1f5f9;font-size:0.78rem;color:#64748b;">
                        <p>📞 {{ config('parish.phone') }}</p>
                        <p style="margin-top:2px;">✉ {{ config('parish.email') }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ═══ MAIN CONTENT ═══ --}}
        <div style="flex:1;min-width:0;">

            @if($services->isEmpty())
            <div style="text-align:center;padding:4rem 0;">
                <svg style="width:64px;height:64px;color:#cbd5e1;margin:0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p style="color:#64748b;font-size:1rem;">No services available at this time.</p>
            </div>
            @else

            @foreach($services as $category => $categoryServices)
            <section class="cat-section" id="{{ Str::slug($category) }}" style="margin-bottom:3rem;">

                {{-- Category header --}}
                <div style="display:flex;align-items:center;gap:0.875rem;margin-bottom:1.5rem;padding-bottom:0.875rem;border-bottom:2px solid #e8edf5;">
                    <div style="width:4px;height:28px;background:linear-gradient(to bottom,#2563eb,#6366f1);border-radius:9999px;flex-shrink:0;"></div>
                    <div>
                        <h2 style="font-size:1.375rem;font-weight:800;color:#0f172a;margin:0;">{{ $category }}</h2>
                        <p style="font-size:0.8rem;color:#64748b;margin:2px 0 0;">{{ $categoryServices->count() }} {{ Str::plural('service', $categoryServices->count()) }}</p>
                    </div>
                </div>

                {{-- Service cards grid --}}
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;">
                    @foreach($categoryServices as $service)
                    <div class="svc-card">

                        {{-- Card header --}}
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem;margin-bottom:0.875rem;">
                            <h3 style="font-size:1rem;font-weight:700;color:#0f172a;line-height:1.3;margin:0;">{{ $service->name }}</h3>
                            @if($service->fee > 0)
                            <span style="display:inline-flex;align-items:center;background:#dcfce7;color:#15803d;font-size:0.8rem;font-weight:700;padding:3px 10px;border-radius:9999px;white-space:nowrap;flex-shrink:0;">
                                ₱{{ number_format($service->fee, 0) }}
                            </span>
                            @else
                            <span style="display:inline-flex;align-items:center;background:#f1f5f9;color:#64748b;font-size:0.8rem;font-weight:600;padding:3px 10px;border-radius:9999px;white-space:nowrap;flex-shrink:0;">
                                Free
                            </span>
                            @endif
                        </div>

                        {{-- Description --}}
                        @if($service->description)
                        <p style="font-size:0.875rem;color:#475569;line-height:1.6;margin-bottom:0.875rem;">{{ $service->description }}</p>
                        @endif

                        {{-- Requirements --}}
                        @if($service->requirements && count($service->requirements))
                        <div style="background:#f8faff;border-radius:0.625rem;padding:0.875rem;margin-bottom:0.875rem;">
                            <p style="font-size:0.7rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#64748b;margin-bottom:0.5rem;">Requirements</p>
                            <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0.375rem;">
                                @foreach($service->requirements as $req)
                                <li style="display:flex;align-items:flex-start;gap:0.5rem;font-size:0.8375rem;color:#374151;line-height:1.5;">
                                    <svg style="width:14px;height:14px;color:#2563eb;flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $req }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        {{-- Duration badge --}}
                        @if($service->duration_minutes)
                        <div style="display:flex;align-items:center;gap:5px;font-size:0.78rem;color:#64748b;margin-bottom:0.875rem;">
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $service->duration_minutes }} minutes
                        </div>
                        @endif

                        {{-- Book button --}}
                        @if($service->is_bookable)
                        @auth
                        <a href="{{ route('parishioner.bookings.create') }}"
                           style="display:flex;align-items:center;justify-content:center;gap:6px;background:#2563eb;color:#fff;font-weight:700;font-size:0.8125rem;padding:0.625rem 1rem;border-radius:0.625rem;text-decoration:none;transition:all 0.2s;margin-top:auto;"
                           onmouseover="this.style.background='#1d4ed8';this.style.transform='translateY(-1px)';"
                           onmouseout="this.style.background='#2563eb';this.style.transform='';">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Book This Service
                        </a>
                        @else
                        <a href="{{ route('register') }}"
                           style="display:flex;align-items:center;justify-content:center;gap:6px;background:#f1f5f9;color:#374151;font-weight:700;font-size:0.8125rem;padding:0.625rem 1rem;border-radius:0.625rem;text-decoration:none;transition:all 0.2s;border:1.5px solid #e2e8f0;"
                           onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
                            Register to Book
                        </a>
                        @endauth
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endforeach

            @endif

            {{-- Bottom CTA --}}
            <div style="background:linear-gradient(135deg,#1e3a8a 0%,#312e81 100%);border-radius:1.25rem;padding:2.5rem;text-align:center;color:#fff;margin-top:1rem;">
                <h3 style="font-size:1.375rem;font-weight:800;margin-bottom:0.75rem;">Can't find what you're looking for?</h3>
                <p style="color:#bfdbfe;font-size:0.9375rem;margin-bottom:1.5rem;">Contact the parish office directly and we'll be happy to assist you.</p>
                <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:0.75rem;">
                    <a href="{{ route('contact') }}"
                       style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e3a8a;font-weight:700;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:9999px;text-decoration:none;transition:all 0.2s;"
                       onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#fff';">
                        Contact Us
                    </a>
                    <a href="{{ route('announcements') }}"
                       style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);color:#fff;font-weight:600;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:9999px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.25);transition:all 0.2s;"
                       onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                        View Announcements
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Highlight active sidebar link on scroll
const sections = document.querySelectorAll('.cat-section');
const sidebarLinks = document.querySelectorAll('.svc-sidebar-link[data-section]');
const catTabs = document.querySelectorAll('.cat-tab');

function updateActive() {
    let current = '';
    sections.forEach(section => {
        const top = section.getBoundingClientRect().top;
        if (top <= 120) current = section.id;
    });

    sidebarLinks.forEach(link => {
        link.classList.toggle('active', link.dataset.section === current);
    });
    catTabs.forEach(tab => {
        const href = tab.getAttribute('href').replace('#', '');
        tab.classList.toggle('active', href === current);
    });
}

window.addEventListener('scroll', updateActive, { passive: true });
updateActive();

// Smooth scroll for sidebar links
document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
        const target = document.querySelector(link.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
@endpush
