@extends('layouts.public')
@section('title', 'About the Parish')

@push('styles')
<style>
.about-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #2563eb 100%);
    position: relative;
    overflow: hidden;
    padding: 5rem 0 4rem;
    color: #fff;
}
.about-hero::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(96,165,250,0.2), transparent 70%);
}
.about-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; left: 10%;
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(129,140,248,0.15), transparent 70%);
}
.section-tag {
    display: inline-block;
    font-size: 0.7rem; font-weight: 700;
    letter-spacing: 0.18em; text-transform: uppercase;
    color: #3b82f6; margin-bottom: 0.5rem;
}
.stat-number {
    font-size: 2.25rem;
    font-weight: 800;
    color: #1e3a8a;
    line-height: 1;
}
.ministry-tag {
    display: inline-flex; align-items: center; gap: 6px;
    background: #eff6ff; color: #1e40af;
    border: 1px solid #bfdbfe;
    font-size: 0.8125rem; font-weight: 600;
    padding: 6px 14px; border-radius: 9999px;
    transition: all 0.2s;
}
.ministry-tag:hover { background: #2563eb; color: #fff; border-color: #2563eb; }
.timeline-dot {
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #2563eb;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #2563eb;
    flex-shrink: 0;
    margin-top: 4px;
}
</style>
@endpush

@section('content')

{{-- ── HERO ── --}}
<section class="about-hero">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row items-center gap-8">
            {{-- Logo --}}
            <div class="flex-shrink-0">
                <div style="width:120px;height:120px;border-radius:50%;border:4px solid rgba(255,255,255,0.5);overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.3);">
                    <img src="{{ asset('images/parish-logo.png') }}" alt="MHC Parish"
                         style="width:100%;height:100%;object-fit:cover;"
                         onerror="this.parentElement.innerHTML='<div style=\'width:100%;height:100%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:2.5rem;\'>⛪</div>'">
                </div>
            </div>
            {{-- Text --}}
            <div>
                <p style="color:#bfdbfe;font-size:0.875rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:0.5rem;">Diocese of San Pablo · Archdiocese of Lipa</p>
                <h1 style="font-size:clamp(1.75rem,4vw,3rem);font-weight:800;line-height:1.15;margin-bottom:0.75rem;">
                    Mary Help of Christians Parish
                </h1>
                <p style="color:#bfdbfe;font-size:1rem;max-width:560px;line-height:1.7;margin-bottom:1.25rem;">
                    Serving the community of Southville 1, Niugan, Cabuyao, Laguna with faith, hope, and charity since 2015.
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:0.75rem;">
                    <a href="{{ route('services') }}"
                       style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#1e3a8a;font-weight:700;font-size:0.875rem;padding:0.75rem 1.5rem;border-radius:9999px;text-decoration:none;box-shadow:0 4px 16px rgba(0,0,0,0.2);transition:all 0.2s;"
                       onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#fff';">
                        Our Services
                    </a>
                    <a href="{{ route('contact') }}"
                       style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);color:#fff;font-weight:600;font-size:0.875rem;padding:0.75rem 1.5rem;border-radius:9999px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.25);transition:all 0.2s;"
                       onmouseover="this.style.background='rgba(255,255,255,0.2)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── STATS ── --}}
<section style="background:#fff;border-bottom:1px solid #f1f5f9;padding:2rem 0;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1.5rem;text-align:center;">
            @foreach([
                ['Est. 2015','Year Founded','📅'],
                ['7','Days of Masses','⛪'],
                ['5+','Active Ministries','🙌'],
                ['Daily','Sacraments','✝️'],
            ] as $s)
            <div style="padding:1rem;">
                <div style="font-size:2rem;margin-bottom:6px;">{{ $s[2] }}</div>
                <div class="stat-number">{{ $s[0] }}</div>
                <div style="font-size:0.8rem;color:#64748b;font-weight:500;margin-top:4px;">{{ $s[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── MAIN CONTENT ── --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">

        {{-- LEFT: Main content --}}
        <div class="lg:col-span-2 space-y-12">

            {{-- History --}}
            <section>
                <span class="section-tag">Who we are</span>
                <h2 style="font-size:1.875rem;font-weight:800;color:#0f172a;margin:0 0 1rem;">Our History</h2>
                <div style="width:48px;height:4px;background:#2563eb;border-radius:9999px;margin-bottom:1.5rem;"></div>
                <div style="font-size:1rem;color:#475569;line-height:1.85;space-y:1rem;">
                    <p style="margin-bottom:1rem;">
                        <strong style="color:#1e3a8a;">Mary Help of Christians Parish</strong> was established to serve the growing Catholic community of
                        Southville 1, Niugan, Cabuyao, Laguna. Named after the Blessed Virgin Mary under her title
                        "Help of Christians" — patroness of those who seek her intercession — the parish has grown
                        from a small community chapel into a vibrant center of faith and service.
                    </p>
                    <p style="margin-bottom:1rem;">
                        We are part of the <strong>Diocese of San Pablo</strong>, under the guidance of our Bishop.
                        The parish feast day is celebrated on <strong>May 24</strong> each year with a special novena,
                        procession, and solemn High Mass attended by the entire community.
                    </p>
                    <p>
                        Today, the parish serves thousands of Catholic families in Southville and surrounding
                        barangays, providing sacramental services, faith formation programs, and outreach activities
                        rooted in the Gospel values of charity and service.
                    </p>
                </div>
            </section>

            {{-- Mission & Vision --}}
            <section>
                <span class="section-tag">Purpose</span>
                <h2 style="font-size:1.875rem;font-weight:800;color:#0f172a;margin:0 0 1rem;">Mission & Vision</h2>
                <div style="width:48px;height:4px;background:#2563eb;border-radius:9999px;margin-bottom:1.5rem;"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
                    <div style="background:linear-gradient(135deg,#eff6ff,#e0e7ff);border-radius:1rem;padding:1.5rem;">
                        <div style="font-size:1.5rem;margin-bottom:0.75rem;">🎯</div>
                        <h3 style="font-size:1rem;font-weight:800;color:#1e3a8a;margin-bottom:0.5rem;">Our Mission</h3>
                        <p style="font-size:0.875rem;color:#374151;line-height:1.7;">
                            To be a vibrant community of faith, rooted in the Gospel, celebrating the sacraments,
                            and serving the poor and marginalized in the spirit of Mary Help of Christians.
                        </p>
                    </div>
                    <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-radius:1rem;padding:1.5rem;">
                        <div style="font-size:1.5rem;margin-bottom:0.75rem;">👁️</div>
                        <h3 style="font-size:1rem;font-weight:800;color:#065f46;margin-bottom:0.5rem;">Our Vision</h3>
                        <p style="font-size:0.875rem;color:#374151;line-height:1.7;">
                            A parish where every family is known, loved, and accompanied on their journey of faith —
                            a true communion of disciples united in prayer and service.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Core Values --}}
            <section>
                <span class="section-tag">What guides us</span>
                <h2 style="font-size:1.875rem;font-weight:800;color:#0f172a;margin:0 0 1rem;">Core Values</h2>
                <div style="width:48px;height:4px;background:#2563eb;border-radius:9999px;margin-bottom:1.5rem;"></div>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;">
                    @foreach([
                        ['🙏','Faith','Rooted in prayer and the Eucharist'],
                        ['❤️','Charity','Serving the poorest among us'],
                        ['🤝','Community','Family-centered parish life'],
                        ['📖','Formation','Ongoing faith education'],
                        ['⚖️','Justice','Working for the common good'],
                        ['✝️','Tradition','Faithful to the Magisterium'],
                    ] as $v)
                    <div style="background:#fff;border:1px solid #e8edf5;border-radius:0.875rem;padding:1.25rem;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.04);transition:all 0.2s;"
                         onmouseover="this.style.boxShadow='0 8px 24px rgba(37,99,235,0.12)';this.style.transform='translateY(-3px)';"
                         onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.04)';this.style.transform='';">
                        <div style="font-size:1.75rem;margin-bottom:0.625rem;">{{ $v[0] }}</div>
                        <div style="font-weight:700;font-size:0.9rem;color:#0f172a;margin-bottom:3px;">{{ $v[1] }}</div>
                        <div style="font-size:0.78rem;color:#64748b;line-height:1.5;">{{ $v[2] }}</div>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- Ministries --}}
            <section>
                <span class="section-tag">Get involved</span>
                <h2 style="font-size:1.875rem;font-weight:800;color:#0f172a;margin:0 0 1rem;">Parish Ministries</h2>
                <div style="width:48px;height:4px;background:#2563eb;border-radius:9999px;margin-bottom:1.5rem;"></div>
                <p style="color:#475569;margin-bottom:1.25rem;font-size:0.9375rem;">
                    Our parish has various ministries where parishioners can grow in faith and service.
                    All are welcome to join!
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:0.625rem;">
                    @foreach([
                        'Basic Ecclesial Communities (BEC)',
                        'Parish Pastoral Council',
                        'Youth Ministry',
                        'Couples for Christ',
                        'Legion of Mary',
                        'Knights of Columbus',
                        'Parish Finance Committee',
                        'Liturgical Ministry',
                        'Social Action Ministry',
                        'Catechetical Ministry',
                        'Music Ministry',
                        'Parish Health Ministry',
                    ] as $ministry)
                    <span class="ministry-tag">{{ $ministry }}</span>
                    @endforeach
                </div>
                <div style="margin-top:1.25rem;padding:1rem 1.25rem;background:#f8faff;border-radius:0.75rem;border:1px solid #bfdbfe;">
                    <p style="font-size:0.875rem;color:#1e40af;font-weight:500;">
                        💬 Interested in joining a ministry? Contact the parish office at
                        <strong>{{ config('parish.phone') }}</strong> or email <strong>{{ config('parish.email') }}</strong>
                    </p>
                </div>
            </section>

        </div>

        {{-- RIGHT: Sidebar --}}
        <aside class="space-y-6">

            {{-- Parish Priest --}}
            <div style="background:#fff;border-radius:1.25rem;border:1px solid #e8edf5;box-shadow:0 4px 16px rgba(0,0,0,0.06);overflow:hidden;">
                <div style="background:linear-gradient(135deg,#1e3a8a,#2563eb);padding:1.5rem;text-align:center;">
                    <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 0.75rem;font-size:2rem;border:3px solid rgba(255,255,255,0.4);">
                        👨‍⚕️
                    </div>
                    <p style="font-size:0.7rem;color:#bfdbfe;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:4px;">Parish Clergy</p>
                    <p style="font-weight:800;font-size:1rem;color:#fff;margin-bottom:2px;">{{ config('parish.priest') }}</p>
                    <p style="font-size:0.8125rem;color:#93c5fd;">Parish Priest</p>
                </div>
                <div style="padding:1.25rem;background:#f8faff;">
                    <p style="font-size:0.8125rem;color:#475569;text-align:center;line-height:1.7;">
                        Leading our parish community with dedication and pastoral care.
                        Available for spiritual direction and sacramental ministry.
                    </p>
                </div>
            </div>

            {{-- Contact Info --}}
            <div style="background:#fff;border-radius:1.25rem;border:1px solid #e8edf5;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <h3 style="font-weight:800;font-size:0.9375rem;color:#0f172a;margin-bottom:1rem;display:flex;align-items:center;gap:8px;">
                    <svg style="width:16px;height:16px;color:#2563eb;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Find Us
                </h3>
                <div style="space-y:0.75rem;font-size:0.875rem;color:#374151;line-height:1.7;">
                    <p style="margin-bottom:0.5rem;">📍 {{ config('parish.address') }}</p>
                    <p style="margin-bottom:0.5rem;">📞 {{ config('parish.phone') }}</p>
                    <p style="margin-bottom:0.75rem;">✉️ {{ config('parish.email') }}</p>
                </div>
                <a href="{{ route('contact') }}"
                   style="display:flex;align-items:center;justify-content:center;gap:6px;background:#2563eb;color:#fff;font-weight:700;font-size:0.8125rem;padding:0.625rem 1rem;border-radius:0.625rem;text-decoration:none;transition:background 0.2s;"
                   onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                    Send us a Message →
                </a>
            </div>

            {{-- Office Hours --}}
            <div style="background:#fff;border-radius:1.25rem;border:1px solid #e8edf5;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <h3 style="font-weight:800;font-size:0.9375rem;color:#0f172a;margin-bottom:1rem;display:flex;align-items:center;gap:8px;">
                    <svg style="width:16px;height:16px;color:#2563eb;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Office Hours
                </h3>
                <div style="font-size:0.875rem;line-height:2;">
                    @foreach(['Tuesday – Friday' => '8AM–12NN, 2PM–5PM', 'Saturday' => '8AM–12NN', 'Sunday' => 'After Masses', 'Monday' => 'Closed'] as $day => $hours)
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #f1f5f9;padding:0.25rem 0;">
                        <span style="color:#64748b;">{{ $day }}</span>
                        <span style="font-weight:600;color:{{ $day === 'Monday' ? '#ef4444' : '#0f172a' }};">{{ $hours }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Quick links --}}
            <div style="background:#fff;border-radius:1.25rem;border:1px solid #e8edf5;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <h3 style="font-weight:800;font-size:0.9375rem;color:#0f172a;margin-bottom:1rem;">Quick Links</h3>
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    @foreach([
                        [route('services'), 'Parish Services & Sacraments', '📋'],
                        [route('announcements'), 'Latest Announcements', '📢'],
                        [route('walkin.index'), 'Walk-in Booking Form', '📝'],
                        [route('register'), 'Register an Account', '👤'],
                    ] as $link)
                    <a href="{{ $link[0] }}"
                       style="display:flex;align-items:center;gap:10px;padding:0.625rem 0.875rem;border-radius:0.625rem;text-decoration:none;font-size:0.875rem;font-weight:500;color:#374151;border:1px solid #e8edf5;transition:all 0.2s;"
                       onmouseover="this.style.background='#eff6ff';this.style.borderColor='#bfdbfe';this.style.color='#2563eb';"
                       onmouseout="this.style.background='';this.style.borderColor='#e8edf5';this.style.color='#374151';">
                        <span>{{ $link[2] }}</span>
                        {{ $link[1] }}
                    </a>
                    @endforeach
                </div>
            </div>

        </aside>
    </div>
</div>

@endsection
