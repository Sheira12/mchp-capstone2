@extends('layouts.public')
@section('title', 'Mass Schedule')
@section('meta-description', 'Weekly Mass schedule and special masses at Mary Help of Christians Parish, Niugan, Cabuyao, Laguna.')

@push('styles')
<style>
.ms-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #1d4ed8 100%);
    padding: 4rem 0 3.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.ms-hero::before {
    content:''; position:absolute; inset:0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.ms-section-title {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.2em;
    text-transform: uppercase; color: #3b82f6; margin-bottom: 0.5rem;
}
.day-card {
    background: #fff; border-radius: 1rem; overflow: hidden;
    border: 1px solid #e8edf5;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: box-shadow 0.2s, transform 0.2s;
}
.day-card:hover { box-shadow: 0 8px 24px rgba(37,99,235,0.1); transform: translateY(-2px); }
.day-head {
    color: #fff; text-align: center; padding: 0.75rem 0.5rem;
    font-weight: 800; font-size: 0.875rem; letter-spacing: 0.04em;
}
.day-head.weekend { background: linear-gradient(135deg, #b8860b, #d4af37); }
.day-head.weekday { background: linear-gradient(135deg, #1e3a8a, #2563eb); }
.mass-slot {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f4ff;
    display: flex; flex-direction: column; align-items: center;
}
.mass-slot:last-child { border-bottom: none; }
.mass-time  { font-weight: 700; font-size: 1rem; color: #1e3a8a; }
.mass-lang  { font-size: 0.7rem; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 1px; }
.mass-cel   { font-size: 0.72rem; color: #64748b; margin-top: 2px; }
.mass-notes { font-size: 0.68rem; color: #94a3b8; font-style: italic; margin-top: 2px; }

.special-card {
    background: #fff; border-radius: 1rem; border: 1px solid #fde68a;
    overflow: hidden; display: flex; align-items: stretch;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.special-date-badge {
    background: linear-gradient(135deg, #b8860b, #d4af37);
    color: #fff; width: 80px; flex-shrink: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 1rem 0.5rem;
}
.special-date-badge .day-num { font-size: 1.75rem; font-weight: 800; line-height: 1; }
.special-date-badge .month   { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
.special-body { padding: 1rem 1.25rem; flex: 1; }
</style>
@endpush

@section('content')

{{-- ── Hero ── --}}
<section class="ms-hero">
    <div class="relative z-10 max-w-3xl mx-auto px-4">
        <p class="inline-block text-xs font-bold tracking-widest uppercase text-blue-300 mb-3">
            Mary Help of Christians Parish
        </p>
        <h1 class="text-3xl sm:text-4xl font-black text-white mb-2">Mass Schedule</h1>
        <p class="text-blue-200 text-sm">Niugan, Cabuyao, Laguna &mdash; All are welcome</p>
    </div>
</section>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-14">

    {{-- ── Weekly Masses ── --}}
    @if($regular->count())
    <section>
        <div class="text-center mb-8">
            <p class="ms-section-title">Every Week</p>
            <h2 class="text-2xl font-bold text-gray-900">Regular Mass Schedule</h2>
            <div class="w-10 h-1 bg-blue-600 rounded-full mx-auto mt-3"></div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3">
            @foreach($regular as $day => $masses)
            @php $isWeekend = in_array($day, [0, 6]); @endphp
            <div class="day-card">
                <div class="day-head {{ $isWeekend ? 'weekend' : 'weekday' }}">
                    {{ $days[$day] ?? 'Special' }}
                </div>
                <div>
                    @foreach($masses->sortBy('time') as $m)
                    <div class="mass-slot">
                        <span class="mass-time">{{ \Carbon\Carbon::parse($m->time)->format('g:i A') }}</span>
                        <span class="mass-lang">{{ $m->language }}</span>
                        @if($m->celebrant)
                        <span class="mass-cel">Fr. {{ $m->celebrant }}</span>
                        @endif
                        @if($m->notes)
                        <span class="mass-notes">{{ $m->notes }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ── Special Masses ── --}}
    @if($special->count())
    <section>
        <div class="text-center mb-8">
            <p class="ms-section-title">Upcoming Special Masses</p>
            <h2 class="text-2xl font-bold text-gray-900">Special &amp; Feast Day Masses</h2>
            <div class="w-10 h-1 bg-yellow-500 rounded-full mx-auto mt-3"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($special as $m)
            <div class="special-card">
                <div class="special-date-badge">
                    <span class="day-num">{{ $m->special_date->format('d') }}</span>
                    <span class="month">{{ $m->special_date->format('M Y') }}</span>
                </div>
                <div class="special-body">
                    <p class="font-bold text-gray-900 text-sm">{{ $m->special_title ?? 'Special Mass' }}</p>
                    <p class="text-blue-700 font-semibold text-sm mt-1">
                        {{ \Carbon\Carbon::parse($m->time)->format('g:i A') }}
                        &mdash; {{ $m->language }}
                    </p>
                    @if($m->celebrant)
                    <p class="text-xs text-gray-500 mt-0.5">Fr. {{ $m->celebrant }}</p>
                    @endif
                    @if($m->notes)
                    <p class="text-xs text-gray-400 mt-1 italic">{{ $m->notes }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ── Confession ── --}}
    @if(isset($officeHours) && $officeHours)
    <section class="bg-blue-50 border border-blue-100 rounded-2xl px-6 py-8 text-center">
        <p class="ms-section-title">Parish Office</p>
        <h2 class="text-xl font-bold text-gray-900 mb-2">Office Hours</h2>
        <p class="text-gray-600">{{ $officeHours }}</p>
    </section>
    @endif

    {{-- ── Contact CTA ── --}}
    <section class="text-center">
        <p class="text-gray-500 text-sm mb-4">For Mass intentions, contact the parish office.</p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('contact') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Contact Us
            </a>
            <a href="{{ route('parishioner.bookings.create') }}"
               class="inline-flex items-center gap-2 bg-white border border-blue-200 hover:bg-blue-50 text-blue-700 font-bold px-5 py-2.5 rounded-xl transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Request Mass Intention
            </a>
        </div>
    </section>

</div>
@endsection
