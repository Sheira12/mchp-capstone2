@extends('layouts.public')
@section('title', 'Announcements')
@section('meta-description', 'Latest announcements and news from Mary Help of Christians Parish')

@push('styles')
<style>
.ann-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);
    padding: 4.5rem 0 3rem;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.ann-hero::before {
    content:'';position:absolute;top:-80px;right:-60px;
    width:340px;height:340px;
    background:radial-gradient(circle,rgba(96,165,250,0.18),transparent 65%);
    pointer-events:none;
}
.ann-hero::after {
    content:'';position:absolute;bottom:-80px;left:-40px;
    width:280px;height:280px;
    background:radial-gradient(circle,rgba(129,140,248,0.12),transparent 70%);
    pointer-events:none;
}
/* Category filter pills */
.cat-pills {
    display:flex;flex-wrap:wrap;gap:0.5rem;
    padding:1.25rem 0;
}
.cat-pill {
    display:inline-flex;align-items:center;gap:5px;
    padding:0.4rem 1rem;border-radius:9999px;
    font-size:0.8125rem;font-weight:600;
    text-decoration:none;
    border:1.5px solid transparent;
    transition:all 0.18s;
    cursor:pointer;
}
.cat-pill-all     { background:#1e3a8a;color:#fff;border-color:#1e3a8a; }
.cat-pill-default { background:#fff;color:#475569;border-color:#e2e8f0; }
.cat-pill-default:hover { background:#eff6ff;color:#2563eb;border-color:#bfdbfe; }
.cat-pill.active  { background:#2563eb;color:#fff;border-color:#2563eb; }
/* Announcement card */
.ann-card {
    background:#fff;
    border-radius:1.25rem;
    border:1px solid #e8edf5;
    overflow:hidden;
    transition:all 0.25s ease;
    text-decoration:none;
    display:flex;flex-direction:column;
    box-shadow:0 2px 10px rgba(0,0,0,0.04);
}
.ann-card:hover {
    box-shadow:0 16px 40px rgba(37,99,235,0.12);
    transform:translateY(-4px);
    border-color:#93c5fd;
}
.ann-card-img { position:relative;height:210px;overflow:hidden;flex-shrink:0; }
.ann-card-img img { width:100%;height:100%;object-fit:cover;transition:transform 0.4s ease; }
.ann-card:hover .ann-card-img img { transform:scale(1.06); }
.ann-cat-badge {
    position:absolute;top:12px;left:12px;
    background:#2563eb;color:#fff;
    font-size:0.65rem;font-weight:700;
    padding:3px 10px;border-radius:9999px;
    text-transform:uppercase;letter-spacing:0.08em;
    box-shadow:0 2px 8px rgba(37,99,235,0.4);
}
.ann-card-body { padding:1.5rem;flex:1;display:flex;flex-direction:column; }
.ann-card-title {
    font-weight:700;font-size:1.0625rem;color:#0f172a;
    margin:0 0 0.5rem;line-height:1.4;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
    transition:color 0.15s;
}
.ann-card:hover .ann-card-title { color:#2563eb; }
.ann-card-excerpt {
    font-size:0.875rem;color:#64748b;flex:1;
    display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
    line-height:1.65;margin:0 0 1rem;
}
.ann-card-foot {
    display:flex;align-items:center;justify-content:space-between;
    padding-top:0.875rem;border-top:1px solid #f1f5f9;
    margin-top:auto;
}
.ann-card-date { font-size:0.75rem;color:#94a3b8; }
.ann-read-more { font-size:0.8125rem;font-weight:700;color:#2563eb;display:flex;align-items:center;gap:4px; }
/* Featured / large card */
.ann-card-featured .ann-card-img { height:280px; }
</style>
@endpush

@section('content')

{{-- Hero --}}
<section class="ann-hero">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div style="max-width:600px;">
            <p style="font-size:0.75rem;font-weight:700;letter-spacing:0.2em;text-transform:uppercase;color:#93c5fd;margin-bottom:0.75rem;">
                Mary Help of Christians Parish
            </p>
            <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:800;line-height:1.15;margin-bottom:0.75rem;">
                Parish Announcements
            </h1>
            <p style="color:#bfdbfe;font-size:1rem;line-height:1.7;margin-bottom:1.5rem;">
                Stay connected with our community. Find the latest news, schedules, and updates from our parish.
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:0.75rem;">
                <a href="{{ route('contact') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.12);color:#fff;font-weight:600;font-size:0.875rem;padding:0.625rem 1.375rem;border-radius:9999px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.2);transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.22)';"
                   onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                    ✉️ Contact Parish
                </a>
                <a href="{{ route('events') }}"
                   style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,0.08);color:#bfdbfe;font-weight:600;font-size:0.875rem;padding:0.625rem 1.375rem;border-radius:9999px;text-decoration:none;border:1.5px solid rgba(255,255,255,0.1);transition:all 0.2s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.15)';"
                   onmouseout="this.style.background='rgba(255,255,255,0.08)';">
                    📅 View Events
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Category filter bar + grid --}}
<section style="padding:3rem 0 5rem;background:#f8faff;min-height:60vh;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if($announcements->isEmpty())
        {{-- Empty state --}}
        <div style="background:#fff;border-radius:1.5rem;border:1px solid #e8edf5;padding:5rem 2rem;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.04);">
            <div style="width:72px;height:72px;background:#f1f5f9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:2rem;">📢</div>
            <h3 style="font-size:1.25rem;font-weight:700;color:#0f172a;margin-bottom:0.5rem;">No announcements yet</h3>
            <p style="color:#64748b;font-size:0.9375rem;margin-bottom:1.5rem;">Check back soon for parish updates and news.</p>
            <a href="{{ route('home') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;font-weight:700;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:9999px;text-decoration:none;box-shadow:0 4px 14px rgba(37,99,235,0.35);">
                ← Back to Home
            </a>
        </div>

        @else

        {{-- Category filter pills (computed from actual data) --}}
        @php
            $categories = $announcements->pluck('category')->unique()->filter()->sort()->values();
            $currentCat = request('category');
            $catColors  = ['Schedule'=>'#3b82f6','Event'=>'#8b5cf6','Announcement'=>'#0891b2','News'=>'#059669','urgent'=>'#dc2626'];
        @endphp
        @if($categories->count() > 1)
        <div class="cat-pills">
            <a href="{{ route('announcements') }}"
               class="cat-pill {{ !$currentCat ? 'active' : 'cat-pill-default' }}">
                🗂 All
                <span style="background:rgba(255,255,255,0.25);padding:1px 6px;border-radius:9999px;font-size:0.7rem;">{{ $announcements->total() }}</span>
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('announcements', ['category' => $cat]) }}"
               class="cat-pill {{ $currentCat === $cat ? 'active' : 'cat-pill-default' }}">
                {{ ucfirst($cat) }}
            </a>
            @endforeach
        </div>
        @endif

        {{-- Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-2">
            @foreach($announcements as $i => $announcement)
            <a href="{{ route('announcements.show', $announcement) }}"
               class="ann-card {{ $i === 0 ? 'ann-card-featured' : '' }}">
                <div class="ann-card-img">
                    @if($announcement->image_path)
                    <img src="{{ Storage::url($announcement->image_path) }}"
                         alt="{{ $announcement->title }}" loading="{{ $i < 3 ? 'eager' : 'lazy' }}">
                    @else
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#dbeafe 0%,#e0e7ff 100%);display:flex;align-items:center;justify-content:center;">
                        <svg width="52" height="52" fill="none" stroke="#93c5fd" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    @endif
                    @if($announcement->category)
                    <span class="ann-cat-badge">{{ $announcement->category }}</span>
                    @endif
                    @if($announcement->expires_at && $announcement->expires_at->isFuture() && $announcement->expires_at->diffInDays() <= 7)
                    <span style="position:absolute;top:12px;right:12px;background:#dc2626;color:#fff;font-size:0.6rem;font-weight:700;padding:2px 8px;border-radius:9999px;text-transform:uppercase;letter-spacing:0.06em;">
                        Expires soon
                    </span>
                    @endif
                </div>
                <div class="ann-card-body">
                    <h3 class="ann-card-title">{{ $announcement->title }}</h3>
                    <p class="ann-card-excerpt">{{ strip_tags($announcement->content) }}</p>
                    <div class="ann-card-foot">
                        <span class="ann-card-date">
                            <svg style="width:12px;height:12px;display:inline;margin-right:3px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $announcement->published_at?->format('M d, Y') ?? 'Unpublished' }}
                        </span>
                        <span class="ann-read-more">
                            Read more
                            <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($announcements->hasPages())
        <div style="margin-top:3rem;">
            {{ $announcements->links() }}
        </div>
        @endif

        @endif
    </div>
</section>

{{-- Bottom CTA --}}
<section style="padding:4rem 0;background:#fff;border-top:1px solid #f1f5f9;">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p style="font-size:0.75rem;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;color:#3b82f6;margin-bottom:0.625rem;">Stay Connected</p>
        <h2 style="font-size:1.625rem;font-weight:800;color:#0f172a;margin-bottom:0.75rem;">Never Miss an Update</h2>
        <p style="color:#64748b;font-size:0.9375rem;max-width:480px;margin:0 auto 2rem;line-height:1.75;">
            Register for a parishioner account to receive notifications about bookings, certificates, and parish announcements.
        </p>
        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:0.875rem;">
            <a href="{{ route('register') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;font-weight:700;font-size:0.9375rem;padding:0.875rem 2.25rem;border-radius:9999px;text-decoration:none;box-shadow:0 4px 18px rgba(37,99,235,0.4);transition:all 0.2s;"
               onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(37,99,235,0.5)';"
               onmouseout="this.style.transform='';this.style.boxShadow='0 4px 18px rgba(37,99,235,0.4)';">
                Create Account — It's Free
            </a>
            <a href="{{ route('contact') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#374151;font-weight:600;font-size:0.9375rem;padding:0.875rem 2.25rem;border-radius:9999px;text-decoration:none;border:1.5px solid #e5e7eb;transition:all 0.2s;"
               onmouseover="this.style.background='#f8faff';this.style.borderColor='#bfdbfe';"
               onmouseout="this.style.background='#fff';this.style.borderColor='#e5e7eb';">
                Contact Us
            </a>
        </div>
    </div>
</section>

@endsection
