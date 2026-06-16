@extends('layouts.public')
@section('title', 'Services & Sacraments')
@section('meta-description', 'Parish services including sacraments, blessings, seminars, and certificates at Mary Help of Christians Parish')

@push('styles')
<style>
/* ── Hero ── */
.svc-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #2563eb 100%);
    padding: 4.5rem 0 3rem;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.svc-hero::before {
    content:''; position:absolute; top:-80px; right:-80px;
    width:320px; height:320px;
    background:radial-gradient(circle,rgba(96,165,250,0.2),transparent 70%);
}
.svc-hero::after {
    content:''; position:absolute; bottom:-60px; left:5%;
    width:260px; height:260px;
    background:radial-gradient(circle,rgba(129,140,248,0.15),transparent 70%);
}

/* ── Category nav strip ── */
.cat-strip {
    background:#fff;
    border-bottom:2px solid #e8edf5;
    position:sticky; top:64px; z-index:40;
    overflow-x:auto; -webkit-overflow-scrolling:touch;
}
.cat-strip::-webkit-scrollbar { height:3px; }
.cat-strip::-webkit-scrollbar-thumb { background:#bfdbfe; border-radius:9999px; }
.cat-nav-inner {
    display:flex; gap:0; min-width:max-content;
    padding:0 1rem;
}
.cat-btn {
    display:flex; align-items:center; gap:8px;
    padding:0.875rem 1.25rem;
    font-size:0.875rem; font-weight:600; color:#64748b;
    text-decoration:none; white-space:nowrap;
    border-bottom:3px solid transparent;
    transition:all 0.2s; background:none; border-top:none; border-left:none; border-right:none;
    cursor:pointer;
}
.cat-btn:hover { color:#2563eb; border-bottom-color:#bfdbfe; background:#f8faff; }
.cat-btn.active { color:#2563eb; font-weight:700; border-bottom-color:#2563eb; }
.cat-btn .cat-count {
    background:#e8edf5; color:#64748b;
    font-size:0.7rem; font-weight:700;
    padding:1px 7px; border-radius:9999px;
    transition:all 0.2s;
}
.cat-btn.active .cat-count { background:#dbeafe; color:#2563eb; }
.cat-btn:hover .cat-count { background:#dbeafe; color:#2563eb; }

/* ── Category section ── */
.cat-section { scroll-margin-top: 120px; }

/* ── Service card ── */
.svc-card {
    background:#fff;
    border-radius:1.25rem;
    border:1.5px solid #e8edf5;
    padding:1.5rem;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
    transition:all 0.25s ease;
    display:flex; flex-direction:column; gap:0.875rem;
    height:100%;
    position:relative;
    overflow:hidden;
}
.svc-card:hover {
    box-shadow:0 12px 32px rgba(37,99,235,0.13);
    transform:translateY(-4px);
    border-color:#93c5fd;
}
.svc-card::before {
    content:'';
    position:absolute; top:0; left:0; right:0; height:4px;
    background:var(--accent,#2563eb);
    opacity:0;
    transition:opacity 0.25s;
}
.svc-card:hover::before { opacity:1; }

/* ── Fee badge ── */
.fee-badge {
    display:inline-flex; align-items:center; gap:4px;
    font-size:0.8125rem; font-weight:700;
    padding:4px 12px; border-radius:9999px;
    flex-shrink:0;
}
.fee-paid { background:#d1fae5; color:#065f46; }
.fee-free { background:#f1f5f9; color:#475569; }

/* ── Book button ── */
.svc-book-btn {
    display:flex; align-items:center; justify-content:center; gap:6px;
    padding:0.75rem 1rem; border-radius:0.875rem;
    font-size:0.875rem; font-weight:700; text-decoration:none;
    transition:all 0.2s; margin-top:auto;
    border:none; cursor:pointer; width:100%;
}
.svc-book-btn.primary {
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
    color:#fff; box-shadow:0 4px 14px rgba(37,99,235,0.3);
}
.svc-book-btn.primary:hover { transform:translateY(-1px); box-shadow:0 8px 20px rgba(37,99,235,0.4); }
.svc-book-btn.secondary {
    background:#f0f4ff; color:#2563eb;
    border:1.5px solid #bfdbfe;
}
.svc-book-btn.secondary:hover { background:#dbeafe; }

/* ── Requirements list ── */
.req-list {
    background:#f8faff; border-radius:0.75rem;
    padding:0.875rem; margin:0; list-style:none;
    display:flex; flex-direction:column; gap:0.375rem;
    border:1px solid #e8f0fe;
}
.req-list li {
    display:flex; align-items:flex-start; gap:8px;
    font-size:0.8375rem; color:#374151; line-height:1.5;
}

/* ── Category icon/color mapping ── */
.cat-icon-wrap {
    width:52px; height:52px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.625rem; flex-shrink:0;
    margin-bottom:0.25rem;
}
</style>
@endpush

@section('content')

{{-- ── HERO ── --}}
<section class="svc-hero">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-2xl mx-auto">
            <p style="color:#bfdbfe;font-size:0.8125rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;margin-bottom:0.625rem;">
                Mary Help of Christians Parish
            </p>
            <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:800;line-height:1.15;margin-bottom:1rem;">
                Services & Sacraments
            </h1>
            <p style="color:#bfdbfe;font-size:1rem;line-height:1.7;margin-bottom:1.75rem;">
                We accompany you in every milestone of your faith journey — from baptism to burial,
                and everything in between.
            </p>
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:0.75rem;">
                @auth
                <a href="{{ route('parishioner.bookings.create') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e3a8a;font-weight:700;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:9999px;text-decoration:none;box-shadow:0 4px 16px rgba(0,0,0,0.2);transition:all 0.2s;"
                   onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#fff';">
                    <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Book a Service Online
                </a>
                @else
                <a href="{{ route('register') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e3a8a;font-weight:700;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:9999px;text-decoration:none;box-shadow:0 4px 16px rgba(0,0,0,0.2);transition:all 0.2s;"
                   onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#fff';">
                    Register to Book Online
                </a>
                @endauth
                <a href="{{ route('walkin.index') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:rgba(212,175,55,0.85);color:#fff;font-weight:700;font-size:0.875rem;padding:0.75rem 1.5rem;border-radius:9999px;text-decoration:none;border:1.5px solid rgba(212,175,55,0.5);transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(212,175,55,1)';" onmouseout="this.style.background='rgba(212,175,55,0.85)';">
                    ✍️ Walk-in Booking
                </a>
                <a href="{{ route('contact') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);color:#fff;font-weight:600;font-size:0.875rem;padding:0.75rem 1.5rem;border-radius:9999px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.25);transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                    Contact Us
                </a>
            </div>
        </div>

        {{-- Stats row --}}
        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:2.5rem;margin-top:2.5rem;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.12);">
            @foreach([
                [$services->sum(fn($s) => count($s)), 'Services Available'],
                ['Daily', 'Sacramental Ministry'],
                ['₱100', 'Certificate Fee'],
                ['Walk-in', 'No Account Needed'],
            ] as $stat)
            <div style="text-align:center;">
                <p style="font-size:1.5rem;font-weight:800;color:#fff;line-height:1;margin:0;">{{ $stat[0] }}</p>
                <p style="font-size:0.7rem;font-weight:600;color:#bfdbfe;letter-spacing:0.08em;text-transform:uppercase;margin:4px 0 0;">{{ $stat[1] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── CATEGORY NAV STRIP ── --}}
<div class="cat-strip">
    <div class="max-w-7xl mx-auto">
        <nav class="cat-nav-inner" id="cat-nav">
            @php
            $catMeta = [
                'Sacraments'   => ['✝️','#3b82f6'],
                'Sacramentals' => ['🙏','#8b5cf6'],
                'Seminars'     => ['📚','#f59e0b'],
                'Mass'         => ['⛪','#ef4444'],
                'Certificates' => ['📜','#10b981'],
            ];
            @endphp
            @foreach($services as $category => $categoryServices)
            @php $meta = $catMeta[$category] ?? ['📋','#2563eb']; @endphp
            <a href="#{{ Str::slug($category) }}" class="cat-btn" data-cat="{{ Str::slug($category) }}">
                <span>{{ $meta[0] }}</span>
                {{ $category }}
                <span class="cat-count">{{ $categoryServices->count() }}</span>
            </a>
            @endforeach
        </nav>
    </div>
</div>

{{-- ── SERVICE SECTIONS ── --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">

    @foreach($services as $category => $categoryServices)
    @php
        $meta = $catMeta[$category] ?? ['📋','#2563eb'];
        $catDescriptions = [
            'Sacraments'   => 'The seven sacraments are the visible signs of God\'s invisible grace. We celebrate each one with reverence and joy.',
            'Sacramentals' => 'Blessed objects and actions that help prepare us to receive and cooperate with God\'s grace in daily life.',
            'Seminars'     => 'Required formation programs to prepare for the sacraments. Sign up at the parish office.',
            'Mass'         => 'The source and summit of Christian life. Request a Mass for your intentions or loved ones.',
            'Certificates' => 'Official parish documents for civil and ecclesiastical purposes. Ready within 1–3 working days.',
        ];
        $catDesc = $catDescriptions[$category] ?? '';
    @endphp

    <section class="cat-section" id="{{ Str::slug($category) }}">

        {{-- Category header --}}
        <div style="display:flex;align-items:flex-start;gap:1.25rem;margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:2px solid #e8edf5;">
            <div class="cat-icon-wrap" style="background:{{ $meta[1] }}18;color:{{ $meta[1] }};">
                {{ $meta[0] }}
            </div>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:0.875rem;flex-wrap:wrap;margin-bottom:0.375rem;">
                    <h2 style="font-size:1.625rem;font-weight:800;color:#0f172a;margin:0;">{{ $category }}</h2>
                    <span style="background:{{ $meta[1] }}18;color:{{ $meta[1] }};font-size:0.75rem;font-weight:700;padding:3px 12px;border-radius:9999px;">
                        {{ $categoryServices->count() }} {{ Str::plural('service', $categoryServices->count()) }}
                    </span>
                </div>
                @if($catDesc)
                <p style="color:#64748b;font-size:0.9375rem;margin:0;max-width:640px;">{{ $catDesc }}</p>
                @endif
            </div>
        </div>

        {{-- Service cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem;">
            @foreach($categoryServices as $service)
            @php
                $svcIcons = [
                    'baptism'=>'💧','wedding'=>'💍','funeral_mass'=>'🕯️',
                    'house_blessing'=>'🏠','car_blessing'=>'🚗','business_blessing'=>'🏪',
                    'sick_call'=>'🙏','pre_baptismal'=>'📚','pre_marriage'=>'💑',
                    'confirmation_catechesis'=>'✝️','mass_intention'=>'⛪',
                    'certificate'=>'📜',
                ];
                $icon = $svcIcons[$service->slug] ?? '📋';
                $accent = $meta[1];
            @endphp

            <div class="svc-card" style="--accent:{{ $accent }};">

                {{-- Card top: icon + name + fee --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem;">
                    <div style="display:flex;align-items:center;gap:0.875rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:{{ $accent }}15;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">
                            {{ $icon }}
                        </div>
                        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;line-height:1.3;margin:0;">
                            {{ $service->name }}
                        </h3>
                    </div>
                    @if($service->fee > 0)
                    <span class="fee-badge fee-paid">₱{{ number_format($service->fee, 0) }}</span>
                    @else
                    <span class="fee-badge fee-free">Free</span>
                    @endif
                </div>

                {{-- Description --}}
                @if($service->description)
                <p style="font-size:0.875rem;color:#475569;line-height:1.65;margin:0;">
                    {{ $service->description }}
                </p>
                @endif

                {{-- Requirements --}}
                @if($service->requirements && count($service->requirements))
                <div>
                    <p style="font-size:0.7rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;margin-bottom:0.5rem;">
                        Requirements
                    </p>
                    <ul class="req-list">
                        @foreach($service->requirements as $req)
                        <li>
                            <svg style="width:14px;height:14px;color:{{ $accent }};flex-shrink:0;margin-top:2px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $req }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Duration --}}
                @if($service->duration_minutes)
                <div style="display:flex;align-items:center;gap:5px;font-size:0.8rem;color:#94a3b8;">
                    <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Approx. {{ $service->duration_minutes }} minutes
                </div>
                @endif

                {{-- Book CTA --}}
                @if($service->is_bookable)
                <div style="margin-top:auto;display:flex;gap:0.625rem;">
                    @auth
                    <a href="{{ route('parishioner.bookings.create') }}"
                       class="svc-book-btn primary" style="flex:1;">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Book Online
                    </a>
                    @else
                    <a href="{{ route('register') }}" class="svc-book-btn secondary" style="flex:1;">
                        Register to Book
                    </a>
                    @endauth
                    <a href="{{ route('walkin.index') }}"
                       class="svc-book-btn secondary"
                       style="flex:0 0 auto;padding:0.75rem;"
                       title="Walk-in Booking">
                        ✍️
                    </a>
                </div>
                @else
                <div style="margin-top:auto;">
                    <a href="{{ route('contact') }}"
                       class="svc-book-btn secondary" style="font-size:0.8125rem;">
                        📞 Inquire at Parish Office
                    </a>
                </div>
                @endif

            </div>
            @endforeach
        </div>
    </section>
    @endforeach

    {{-- ── BOTTOM CTA ── --}}
    <div style="background:linear-gradient(135deg,#1e3a8a 0%,#312e81 100%);border-radius:1.5rem;padding:3rem 2rem;text-align:center;color:#fff;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-60px;right:-60px;width:240px;height:240px;background:radial-gradient(circle,rgba(96,165,250,0.15),transparent 70%);"></div>
        <div style="position:absolute;bottom:-60px;left:-40px;width:200px;height:200px;background:radial-gradient(circle,rgba(129,140,248,0.12),transparent 70%);"></div>
        <div style="position:relative;z-index:1;">
            <p style="font-size:0.8125rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:#bfdbfe;margin-bottom:0.75rem;">Need Help?</p>
            <h3 style="font-size:1.75rem;font-weight:800;margin-bottom:0.75rem;">Can't find what you're looking for?</h3>
            <p style="color:#bfdbfe;font-size:0.9375rem;margin-bottom:1.75rem;max-width:480px;margin-left:auto;margin-right:auto;">
                Contact the parish office directly. We're happy to assist you with any service or question.
            </p>
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:0.875rem;">
                <a href="{{ route('contact') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e3a8a;font-weight:700;font-size:0.9rem;padding:0.875rem 2rem;border-radius:9999px;text-decoration:none;transition:all 0.2s;box-shadow:0 4px 16px rgba(0,0,0,0.25);"
                   onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#fff';">
                    📞 Contact Us
                </a>
                <a href="{{ route('walkin.index') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:rgba(212,175,55,0.85);color:#fff;font-weight:700;font-size:0.9rem;padding:0.875rem 2rem;border-radius:9999px;text-decoration:none;border:1.5px solid rgba(212,175,55,0.5);transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(212,175,55,1)';" onmouseout="this.style.background='rgba(212,175,55,0.85)';">
                    ✍️ Walk-in Booking
                </a>
                <a href="{{ route('announcements') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);color:#fff;font-weight:600;font-size:0.9rem;padding:0.875rem 2rem;border-radius:9999px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.25);transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                    📢 Announcements
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Highlight active category in nav on scroll
const sections = document.querySelectorAll('.cat-section');
const catBtns = document.querySelectorAll('.cat-btn[data-cat]');

function updateCatNav() {
    let active = '';
    sections.forEach(s => {
        const top = s.getBoundingClientRect().top;
        if (top <= 140) active = s.id;
    });
    catBtns.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.cat === active);
    });
}

window.addEventListener('scroll', updateCatNav, { passive: true });
updateCatNav();

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const t = document.querySelector(a.getAttribute('href'));
        if (t) { e.preventDefault(); t.scrollIntoView({ behavior:'smooth', block:'start' }); }
    });
});
</script>
@endpush
