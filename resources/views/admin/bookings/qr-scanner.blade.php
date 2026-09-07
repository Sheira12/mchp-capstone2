@extends('layouts.app')
@section('title', 'Walk-in QR Verification')
@section('page-title', 'QR Code Scanner')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Walk-in QR Verification</h1>
            <p class="text-sm text-gray-500 mt-0.5">Scan a parishioner's booking QR code to verify and look up their booking.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}"
           class="text-sm text-blue-600 hover:underline flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            All Bookings
        </a>
    </div>

    {{-- Main grid: scanner (wider) | result+bookings --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        {{-- ── LEFT: Scanner (7 of 12 cols) ───────────────────────────── --}}
        <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">

                {{-- Card header --}}
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 flex items-center gap-4 flex-shrink-0">
                    <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-white text-base">Camera QR Scanner</p>
                        <p class="text-blue-200 text-sm">Point camera at the parishioner's QR code</p>
                    </div>
                    {{-- LIVE badge --}}
                    <div id="live-badge" class="hidden items-center gap-2 bg-white/20 rounded-full px-4 py-1.5 flex-shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-400 animate-pulse inline-block"></span>
                        <span class="text-white text-sm font-bold tracking-wide">LIVE</span>
                    </div>
                </div>

                <div class="p-5 flex flex-col gap-4 flex-1">

                    {{-- Camera viewport — tall and prominent --}}
                    <div class="relative rounded-2xl overflow-hidden bg-gray-950 flex-1"
                         style="min-height: 430px;">

                        <video id="qr-video"
                               class="absolute inset-0 w-full h-full"
                               style="object-fit:cover;transform:scale(0.75);transform-origin:center center;"
                               playsinline muted></video>

                        {{-- Scanning overlay --}}
                        <div id="scan-overlay" class="absolute inset-0 pointer-events-none hidden">
                            {{-- Vignette --}}
                            <div class="absolute inset-0"
                                 style="background:radial-gradient(ellipse at center,transparent 38%,rgba(0,0,0,.6) 100%);">
                            </div>
                            {{-- Target frame --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="relative" style="width:46%;aspect-ratio:1;">
                                    <div class="absolute top-0 left-0 w-10 h-10 border-t-4 border-l-4 border-blue-400 rounded-tl-2xl"></div>
                                    <div class="absolute top-0 right-0 w-10 h-10 border-t-4 border-r-4 border-blue-400 rounded-tr-2xl"></div>
                                    <div class="absolute bottom-0 left-0 w-10 h-10 border-b-4 border-l-4 border-blue-400 rounded-bl-2xl"></div>
                                    <div class="absolute bottom-0 right-0 w-10 h-10 border-b-4 border-r-4 border-blue-400 rounded-br-2xl"></div>
                                    <div id="scan-line"
                                         class="absolute left-2 right-2 h-0.5 rounded-full"
                                         style="background:linear-gradient(90deg,transparent,#60a5fa,transparent);
                                                top:10%;animation:scanline 2s ease-in-out infinite;
                                                box-shadow:0 0 10px 3px rgba(96,165,250,.7);">
                                    </div>
                                </div>
                            </div>
                            <div class="absolute bottom-4 inset-x-0 flex justify-center">
                                <span class="text-white/80 text-sm bg-black/50 rounded-full px-4 py-1.5 font-medium">
                                    Align QR code within the frame
                                </span>
                            </div>
                        </div>

                        {{-- Idle / stopped state --}}
                        <div id="camera-status"
                             class="absolute inset-0 flex flex-col items-center justify-center bg-gray-950/92 transition-all">
                            <div class="text-center text-white px-8 max-w-xs">
                                <div id="status-icon"
                                     class="w-20 h-20 rounded-2xl bg-gray-800/80 flex items-center justify-center mx-auto mb-4 ring-1 ring-gray-700">
                                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                                    </svg>
                                </div>
                                <p id="status-title" class="font-bold text-lg">Camera not started</p>
                                <p id="status-sub" class="text-sm text-gray-400 mt-1.5">Click Start Camera below</p>
                            </div>
                        </div>

                        {{-- Scan success flash --}}
                        <div id="scan-flash"
                             class="absolute inset-0 bg-green-400/25 hidden items-center justify-center pointer-events-none">
                            <div class="bg-white rounded-2xl px-6 py-4 shadow-2xl flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">QR Detected!</p>
                                    <p class="text-xs text-gray-500">Verifying booking…</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Controls --}}
                    <div class="flex gap-3 flex-shrink-0">
                        <button id="start-btn" onclick="startCamera()"
                                class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold py-3.5 rounded-xl transition-all shadow-sm shadow-blue-200 text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/>
                            </svg>
                            Start Camera
                        </button>
                        <button id="stop-btn" onclick="stopCamera()" disabled
                                class="flex-1 flex items-center justify-center gap-2 bg-gray-100 text-gray-400 font-bold py-3.5 rounded-xl transition-all cursor-not-allowed text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z"/>
                            </svg>
                            Stop Camera
                        </button>
                    </div>

                    {{-- Divider --}}
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <div class="flex-1 h-px bg-gray-100"></div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">or enter manually</span>
                        <div class="flex-1 h-px bg-gray-100"></div>
                    </div>

                    {{-- Manual input --}}
                    <div class="flex gap-2 flex-shrink-0">
                        <input id="manual-token" type="text"
                               placeholder="Paste QR token or full verification URL…"
                               class="flex-1 text-sm border border-gray-200 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent font-mono placeholder:font-sans placeholder:text-gray-400 bg-gray-50"
                               onkeydown="if(event.key==='Enter') verifyManual()">
                        <button onclick="verifyManual()"
                                class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-sm px-6 py-3 rounded-xl transition-all shadow-sm shadow-indigo-200 whitespace-nowrap">
                            Verify
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── RIGHT: Result + Today's Bookings (5 of 12 cols) ────────── --}}
        <div class="lg:col-span-5 flex flex-col gap-5">

            {{-- Idle placeholder --}}
            <div id="idle-panel"
                 class="bg-white rounded-2xl border border-dashed border-gray-200 p-10 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-blue-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 18.75h.75v.75h-.75v-.75zM18.75 13.5h.75v.75h-.75v-.75zM18.75 18.75h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                    </svg>
                </div>
                <p class="font-semibold text-gray-700">Scan result will appear here</p>
                <p class="text-sm text-gray-400 mt-1.5">Start the camera and point it at a QR code,<br>or paste a token below</p>
            </div>

            {{-- Result panel --}}
            <div id="result-panel" class="hidden">

                {{-- Loading --}}
                <div id="result-loading"
                     class="hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-10 flex-col items-center justify-center gap-4">
                    <div class="w-12 h-12 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="font-semibold text-gray-600">Verifying QR code…</p>
                </div>

                {{-- Success --}}
                <div id="result-success"
                     class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-white text-lg">Verified ✓</p>
                            <p class="text-green-100 text-sm">Booking found and authenticated</p>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- Parishioner chip --}}
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                            <div id="r-avatar"
                                 class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-extrabold text-xl flex-shrink-0">
                            </div>
                            <div class="min-w-0 flex-1">
                                <p id="r-name" class="font-bold text-gray-900 text-lg truncate"></p>
                                <p id="r-contact" class="text-sm text-gray-500 truncate"></p>
                            </div>
                        </div>

                        {{-- Detail grid --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 rounded-xl p-3.5">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Service</p>
                                <p id="r-type" class="font-bold text-gray-900 text-sm leading-tight"></p>
                                <p id="r-ref" class="text-xs font-mono text-gray-400 mt-1"></p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3.5">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Schedule</p>
                                <p id="r-date" class="font-semibold text-gray-900 text-sm"></p>
                                <p id="r-time" class="text-xs text-gray-500 mt-0.5"></p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3.5">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Status</p>
                                <span id="r-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"></span>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3.5">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Payment</p>
                                <span id="r-payment" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"></span>
                                <p id="r-fee" class="text-xs text-gray-500 mt-1"></p>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 pt-1">
                            <a id="r-link" href="#"
                               class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-3 rounded-xl transition-all shadow-sm shadow-blue-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                View Full Booking
                            </a>
                            <button onclick="resetScanner()"
                                    class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm px-4 py-3 rounded-xl transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                                Scan Again
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Error --}}
                <div id="result-error"
                     class="hidden bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-white/25 flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-white text-lg">Verification Failed</p>
                            <p id="error-msg" class="text-red-100 text-sm mt-0.5"></p>
                        </div>
                    </div>
                    <div class="p-5">
                        <button onclick="resetScanner()"
                                class="w-full flex items-center justify-center gap-2 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-sm py-3 rounded-xl transition border border-red-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                            </svg>
                            Try Again
                        </button>
                    </div>
                </div>
            </div>

            {{-- Today's Bookings --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Today's Bookings</h3>
                    <span class="text-sm text-gray-400">{{ now()->format('M d, Y') }}</span>
                </div>
                @php
                    $todayBookings = \App\Models\Booking::with('parishioner')
                        ->whereDate('scheduled_date', today())
                        ->whereIn('status', ['pending','confirmed'])
                        ->orderBy('scheduled_time')
                        ->take(8)
                        ->get();
                @endphp
                @if($todayBookings->isEmpty())
                    <div class="p-10 text-center">
                        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-400">No bookings scheduled for today.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach($todayBookings as $b)
                        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition cursor-pointer"
                             onclick="window.location='{{ route('admin.bookings.show', $b) }}'">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-extrabold text-sm flex-shrink-0">
                                {{ substr($b->parishioner->first_name ?? '?', 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $b->parishioner->full_name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $b->getTypeLabel() }}</p>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <p class="text-xs font-mono text-gray-500 mb-0.5">
                                    {{ $b->scheduled_time ? \Carbon\Carbon::parse($b->scheduled_time)->format('g:i A') : 'TBD' }}
                                </p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $b->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $b->getStatusLabel() }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<style>
@keyframes scanline {
    0%   { top: 8%; }
    50%  { top: 88%; }
    100% { top: 8%; }
}
</style>
<script>
let videoStream  = null;
let scanInterval = null;
let scanning     = false;
let lastToken    = null;
let processing   = false;

const video  = document.getElementById('qr-video');
const canvas = document.createElement('canvas');
const ctx    = canvas.getContext('2d');

async function startCamera() {
    const startBtn = document.getElementById('start-btn');
    const stopBtn  = document.getElementById('stop-btn');

    startBtn.disabled = true;
    startBtn.innerHTML = `<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992"/></svg> Starting…`;

    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } }
        });
        video.srcObject = videoStream;
        await video.play();

        document.getElementById('camera-status').classList.add('hidden');
        document.getElementById('scan-overlay').classList.remove('hidden');
        const lb = document.getElementById('live-badge');
        lb.classList.remove('hidden');
        lb.classList.add('flex');

        stopBtn.disabled  = false;
        stopBtn.className = 'flex-1 flex items-center justify-center gap-2 bg-red-50 border-2 border-red-200 text-red-700 font-bold py-3.5 rounded-xl transition-all hover:bg-red-100 active:scale-95 text-sm';

        startBtn.disabled = true;
        startBtn.className = 'flex-1 flex items-center justify-center gap-2 bg-gray-100 text-gray-400 font-bold py-3.5 rounded-xl cursor-not-allowed text-sm';
        startBtn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg> Camera Active`;

        scanning = true;
        scanInterval = setInterval(scanFrame, 150);

    } catch (err) {
        startBtn.disabled = false;
        startBtn.className = 'flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold py-3.5 rounded-xl transition-all shadow-sm shadow-blue-200 text-sm';
        startBtn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg> Start Camera`;

        document.getElementById('status-title').textContent = 'Camera access denied';
        document.getElementById('status-sub').textContent   = err.message;
    }
}

function stopCamera() {
    scanning = false;
    clearInterval(scanInterval);
    if (videoStream) { videoStream.getTracks().forEach(t => t.stop()); videoStream = null; }
    video.srcObject = null;

    document.getElementById('scan-overlay').classList.add('hidden');
    document.getElementById('camera-status').classList.remove('hidden');
    const lb = document.getElementById('live-badge');
    lb.classList.add('hidden'); lb.classList.remove('flex');

    document.getElementById('status-title').textContent = 'Camera stopped';
    document.getElementById('status-sub').textContent   = 'Click Start Camera to scan again';

    const startBtn = document.getElementById('start-btn');
    const stopBtn  = document.getElementById('stop-btn');
    startBtn.disabled = false;
    startBtn.className = 'flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold py-3.5 rounded-xl transition-all shadow-sm shadow-blue-200 text-sm';
    startBtn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg> Start Camera`;
    stopBtn.disabled  = true;
    stopBtn.className = 'flex-1 flex items-center justify-center gap-2 bg-gray-100 text-gray-400 font-bold py-3.5 rounded-xl cursor-not-allowed text-sm';
}

function scanFrame() {
    if (!scanning || processing || video.readyState !== video.HAVE_ENOUGH_DATA) return;
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });
    if (code && code.data) {
        const token = extractToken(code.data);
        if (token && token !== lastToken) {
            lastToken  = token;
            processing = true;
            const flash = document.getElementById('scan-flash');
            flash.classList.remove('hidden'); flash.classList.add('flex');
            setTimeout(() => { flash.classList.add('hidden'); flash.classList.remove('flex'); }, 900);
            stopCamera();
            verifyToken(token);
        }
    }
}

function extractToken(raw) {
    const m = raw.match(/\/verify\/([a-f0-9]+)/i);
    return m ? m[1] : raw.trim();
}

function verifyManual() {
    const v = document.getElementById('manual-token').value.trim();
    if (!v) return;
    verifyToken(extractToken(v));
}

async function verifyToken(token) {
    document.getElementById('idle-panel').classList.add('hidden');
    document.getElementById('result-panel').classList.remove('hidden');
    const loading = document.getElementById('result-loading');
    loading.classList.remove('hidden'); loading.classList.add('flex');
    document.getElementById('result-success').classList.add('hidden');
    document.getElementById('result-error').classList.add('hidden');

    try {
        const res  = await fetch('{{ route("admin.bookings.qr-verify") }}', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
            body: JSON.stringify({ token }),
        });
        const data = await res.json();
        loading.classList.add('hidden'); loading.classList.remove('flex');
        data.valid && data.booking ? showSuccess(data.booking) : showError(data.message || 'QR code not recognized.');
    } catch(e) {
        loading.classList.add('hidden'); loading.classList.remove('flex');
        showError('Network error. Please try again.');
    }
}

function showSuccess(b) {
    document.getElementById('r-avatar').textContent  = (b.parishioner||'?')[0].toUpperCase();
    document.getElementById('r-name').textContent    = b.parishioner || '';
    document.getElementById('r-contact').textContent = b.contact     || '';
    document.getElementById('r-type').textContent    = b.type        || '';
    document.getElementById('r-ref').textContent     = b.reference   || '';
    document.getElementById('r-date').textContent    = b.scheduled_date || '';
    document.getElementById('r-time').textContent    = b.scheduled_time || '';
    document.getElementById('r-fee').textContent     = b.service_fee > 0 ? '₱'+parseFloat(b.service_fee).toLocaleString('en-PH',{minimumFractionDigits:2}) : 'Free';
    document.getElementById('r-link').href           = b.url || '#';

    const sc = {pending:'bg-amber-100 text-amber-800',confirmed:'bg-green-100 text-green-800',completed:'bg-blue-100 text-blue-800',cancelled:'bg-red-100 text-red-800'};
    const se = document.getElementById('r-status');
    se.textContent = b.status_label || b.status;
    se.className   = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold '+(sc[b.status]||'bg-gray-100 text-gray-800');

    const pc = {paid:'bg-green-100 text-green-800',pending:'bg-amber-100 text-amber-800',unpaid:'bg-gray-100 text-gray-600',failed:'bg-red-100 text-red-700'};
    const ps = b.payment_status || 'unpaid';
    const pe = document.getElementById('r-payment');
    pe.textContent = ps.charAt(0).toUpperCase()+ps.slice(1);
    pe.className   = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold '+(pc[ps]||'bg-gray-100 text-gray-600');

    document.getElementById('result-success').classList.remove('hidden');
}

function showError(msg) {
    document.getElementById('error-msg').textContent = msg;
    document.getElementById('result-error').classList.remove('hidden');
}

function resetScanner() {
    lastToken = null; processing = false;
    document.getElementById('result-panel').classList.add('hidden');
    document.getElementById('result-success').classList.add('hidden');
    document.getElementById('result-error').classList.add('hidden');
    document.getElementById('idle-panel').classList.remove('hidden');
    document.getElementById('manual-token').value = '';
}

window.addEventListener('beforeunload', stopCamera);
</script>
@endpush

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Walk-in QR Verification</h1>
            <p class="text-sm text-gray-500 mt-0.5">Scan a parishioner's booking QR code to verify and look up their booking.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}"
           class="text-sm text-blue-600 hover:underline flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            All Bookings
        </a>
    </div>

    {{-- Main grid: scanner left, result right on large screens --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- ── LEFT: Scanner Card ───────────────────────────────────────── --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Card header --}}
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-5 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-white">Camera QR Scanner</p>
                        <p class="text-blue-200 text-xs">Point camera at the parishioner's QR code</p>
                    </div>
                    {{-- Live indicator --}}
                    <div id="live-badge" class="ml-auto hidden items-center gap-1.5 bg-white/20 rounded-full px-3 py-1">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse inline-block"></span>
                        <span class="text-white text-xs font-semibold">LIVE</span>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    {{-- Camera viewport --}}
                    <div class="relative rounded-xl overflow-hidden bg-gray-950"
                         style="aspect-ratio:4/3;">

                        <video id="qr-video" class="absolute inset-0 w-full h-full object-cover"
                               playsinline muted></video>

                        {{-- Scanning overlay (shown when active) --}}
                        <div id="scan-overlay" class="absolute inset-0 pointer-events-none hidden">
                            {{-- Dark vignette --}}
                            <div class="absolute inset-0"
                                 style="background:radial-gradient(ellipse at center, transparent 45%, rgba(0,0,0,.55) 100%);">
                            </div>
                            {{-- Target frame --}}
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="relative" style="width:54%;aspect-ratio:1;">
                                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-400 rounded-tl-xl"></div>
                                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-400 rounded-tr-xl"></div>
                                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-400 rounded-bl-xl"></div>
                                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-400 rounded-br-xl"></div>
                                    {{-- Animated scan line --}}
                                    <div id="scan-line"
                                         class="absolute left-1 right-1 h-0.5 rounded-full"
                                         style="background:linear-gradient(90deg,transparent,#60a5fa,transparent);
                                                top:10%;animation:scanline 2s ease-in-out infinite;
                                                box-shadow:0 0 8px 2px rgba(96,165,250,.6);">
                                    </div>
                                </div>
                            </div>
                            {{-- "Align QR code" hint --}}
                            <div class="absolute bottom-3 inset-x-0 flex justify-center">
                                <span class="text-white/70 text-xs bg-black/40 rounded-full px-3 py-1">
                                    Align QR code within the frame
                                </span>
                            </div>
                        </div>

                        {{-- Idle / success / error state overlay --}}
                        <div id="camera-status"
                             class="absolute inset-0 flex flex-col items-center justify-center bg-gray-950/90 transition-all">
                            <div class="text-center text-white px-6">
                                <div id="status-icon" class="w-16 h-16 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                                    </svg>
                                </div>
                                <p id="status-title" class="font-semibold text-sm">Camera not started</p>
                                <p id="status-sub" class="text-xs text-gray-400 mt-1">Click Start Camera below</p>
                            </div>
                        </div>

                        {{-- Scan success flash --}}
                        <div id="scan-flash"
                             class="absolute inset-0 bg-green-400/30 hidden items-center justify-center pointer-events-none">
                            <div class="bg-white rounded-2xl p-4 shadow-2xl flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <span class="font-bold text-gray-900 text-sm">QR Detected!</span>
                            </div>
                        </div>
                    </div>

                    {{-- Camera Controls --}}
                    <div class="flex gap-3">
                        <button id="start-btn" onclick="startCamera()"
                                class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-sm py-3 rounded-xl transition-all shadow-sm shadow-blue-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/>
                            </svg>
                            Start Camera
                        </button>
                        <button id="stop-btn" onclick="stopCamera()" disabled
                                class="flex-1 flex items-center justify-center gap-2 bg-gray-100 text-gray-400 font-bold text-sm py-3 rounded-xl transition-all cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z"/>
                            </svg>
                            Stop Camera
                        </button>
                    </div>

                    {{-- Manual token entry --}}
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 w-px bg-gray-200 mx-5"></div>
                        <div class="flex items-center gap-2 my-1">
                            <div class="flex-1 h-px bg-gray-100"></div>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-2">
                                or enter manually
                            </span>
                            <div class="flex-1 h-px bg-gray-100"></div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <input id="manual-token" type="text"
                               placeholder="Paste QR token or full verification URL…"
                               class="flex-1 text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent font-mono placeholder:font-sans placeholder:text-gray-400 bg-gray-50"
                               onkeydown="if(event.key==='Enter') verifyManual()">
                        <button onclick="verifyManual()"
                                class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-sm px-5 py-2.5 rounded-xl transition-all shadow-sm shadow-indigo-200">
                            Verify
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT: Result + Today's Bookings ────────────────────────── --}}
        <div class="lg:col-span-2 flex flex-col gap-5">

            {{-- Idle placeholder (shown when no scan yet) --}}
            <div id="idle-panel" class="bg-white rounded-2xl border border-dashed border-gray-200 p-8 flex flex-col items-center justify-center text-center min-h-48">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 18.75h.75v.75h-.75v-.75zM18.75 13.5h.75v.75h-.75v-.75zM18.75 18.75h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">Scan result will appear here</p>
                <p class="text-xs text-gray-400 mt-1">Start the camera and scan a QR code,<br>or paste a token in the field</p>
            </div>

            {{-- Result panel (hidden until scan) --}}
            <div id="result-panel" class="hidden">

                {{-- Loading --}}
                <div id="result-loading"
                     class="hidden bg-white rounded-2xl border border-gray-100 shadow-sm p-8 flex flex-col items-center justify-center gap-3">
                    <div class="w-10 h-10 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                    <p class="text-sm font-semibold text-gray-600">Verifying QR code…</p>
                </div>

                {{-- Success --}}
                <div id="result-success" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-5 py-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/25 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-white">Verified ✓</p>
                            <p class="text-green-100 text-xs">Booking found and authenticated</p>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- Parishioner info --}}
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div id="r-avatar"
                                 class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-extrabold text-lg flex-shrink-0">
                            </div>
                            <div class="min-w-0">
                                <p id="r-name" class="font-bold text-gray-900 text-base truncate"></p>
                                <p id="r-contact" class="text-xs text-gray-500"></p>
                            </div>
                        </div>

                        {{-- Booking details --}}
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Service</p>
                                <p id="r-type" class="font-bold text-gray-900 text-sm leading-tight"></p>
                                <p id="r-ref" class="text-xs font-mono text-gray-400 mt-0.5"></p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Schedule</p>
                                <p id="r-date" class="font-semibold text-gray-900 text-sm"></p>
                                <p id="r-time" class="text-xs text-gray-500"></p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Status</p>
                                <span id="r-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"></span>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Payment</p>
                                <span id="r-payment" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"></span>
                                <p id="r-fee" class="text-xs text-gray-500 mt-0.5"></p>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 pt-1">
                            <a id="r-link" href="#"
                               class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-2.5 rounded-xl transition-all shadow-sm shadow-blue-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                View Booking
                            </a>
                            <button onclick="resetScanner()"
                                    class="flex items-center justify-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm py-2.5 px-4 rounded-xl transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                                Scan Again
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Error --}}
                <div id="result-error" class="hidden bg-white rounded-2xl shadow-sm border border-red-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-500 to-rose-600 px-5 py-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white/25 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-white">Verification Failed</p>
                            <p id="error-msg" class="text-red-100 text-xs mt-0.5"></p>
                        </div>
                    </div>
                    <div class="p-4">
                        <button onclick="resetScanner()"
                                class="w-full flex items-center justify-center gap-2 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-sm py-2.5 rounded-xl transition border border-red-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                            </svg>
                            Try Again
                        </button>
                    </div>
                </div>
            </div>

            {{-- Today's Bookings --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 text-sm">Today's Bookings</h3>
                    <span class="text-xs text-gray-400">{{ now()->format('M d, Y') }}</span>
                </div>
                @php
                    $todayBookings = \App\Models\Booking::with('parishioner')
                        ->whereDate('scheduled_date', today())
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->orderBy('scheduled_time')
                        ->take(8)
                        ->get();
                @endphp
                @if($todayBookings->isEmpty())
                    <div class="p-8 text-center">
                        <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                        </svg>
                        <p class="text-sm text-gray-400">No bookings scheduled for today.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach($todayBookings as $b)
                        <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition cursor-pointer"
                             onclick="window.location='{{ route('admin.bookings.show', $b) }}'">
                            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-extrabold text-sm flex-shrink-0">
                                {{ substr($b->parishioner->first_name ?? '?', 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $b->parishioner->full_name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $b->getTypeLabel() }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-xs font-mono text-gray-500">
                                    {{ $b->scheduled_time ? \Carbon\Carbon::parse($b->scheduled_time)->format('g:i A') : 'TBD' }}
                                </p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold mt-0.5
                                    {{ $b->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $b->getStatusLabel() }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<style>
@keyframes scanline {
    0%   { top: 8%; }
    50%  { top: 88%; }
    100% { top: 8%; }
}
</style>
<script>
let videoStream  = null;
let scanInterval = null;
let scanning     = false;
let lastToken    = null;
let processing   = false;

const video  = document.getElementById('qr-video');
const canvas = document.createElement('canvas');
const ctx    = canvas.getContext('2d');

// ── Camera ──────────────────────────────────────────────────────────────────

async function startCamera() {
    const startBtn = document.getElementById('start-btn');
    const stopBtn  = document.getElementById('stop-btn');

    startBtn.disabled = true;
    startBtn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992"/></svg> Starting…`;

    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 960 } }
        });
        video.srcObject = videoStream;
        await video.play();

        document.getElementById('camera-status').classList.add('hidden');
        document.getElementById('scan-overlay').classList.remove('hidden');
        document.getElementById('live-badge').classList.remove('hidden');
        document.getElementById('live-badge').classList.add('flex');

        stopBtn.disabled = false;
        stopBtn.className = 'flex-1 flex items-center justify-center gap-2 bg-red-50 border border-red-200 text-red-700 font-bold text-sm py-3 rounded-xl transition-all hover:bg-red-100 active:scale-95';

        startBtn.disabled = true;
        startBtn.className = 'flex-1 flex items-center justify-center gap-2 bg-gray-100 text-gray-400 font-bold text-sm py-3 rounded-xl cursor-not-allowed';
        startBtn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg> Camera Active`;

        scanning = true;
        scanInterval = setInterval(scanFrame, 150);

    } catch (err) {
        startBtn.disabled = false;
        startBtn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg> Start Camera`;

        document.getElementById('camera-status').classList.remove('hidden');
        document.getElementById('status-icon').innerHTML = `<svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M12 18.75H4.5a2.25 2.25 0 01-2.25-2.25V9m12.841 9.091L16.5 19.5m-1.409-1.409c.407-.407.659-.97.659-1.591v-9a2.25 2.25 0 00-2.25-2.25h-9c-.621 0-1.184.252-1.591.659m12.182 12.182L2.909 5.909M1.5 4.5l1.409 1.409"/>`;
        document.getElementById('status-title').textContent = 'Camera access denied';
        document.getElementById('status-sub').textContent   = err.message;
    }
}

function stopCamera() {
    scanning = false;
    clearInterval(scanInterval);

    if (videoStream) {
        videoStream.getTracks().forEach(t => t.stop());
        videoStream = null;
    }
    video.srcObject = null;

    document.getElementById('scan-overlay').classList.add('hidden');
    document.getElementById('camera-status').classList.remove('hidden');
    document.getElementById('live-badge').classList.add('hidden');
    document.getElementById('live-badge').classList.remove('flex');

    document.getElementById('status-icon').innerHTML = `<svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>`;
    document.getElementById('status-title').textContent = 'Camera stopped';
    document.getElementById('status-sub').textContent   = 'Click Start Camera to scan again';

    const startBtn = document.getElementById('start-btn');
    const stopBtn  = document.getElementById('stop-btn');
    startBtn.disabled = false;
    startBtn.className = 'flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white font-bold text-sm py-3 rounded-xl transition-all shadow-sm shadow-blue-200';
    startBtn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z"/></svg> Start Camera`;
    stopBtn.disabled  = true;
    stopBtn.className = 'flex-1 flex items-center justify-center gap-2 bg-gray-100 text-gray-400 font-bold text-sm py-3 rounded-xl transition-all cursor-not-allowed';
}

// ── QR scanning ──────────────────────────────────────────────────────────────

function scanFrame() {
    if (!scanning || processing || video.readyState !== video.HAVE_ENOUGH_DATA) return;
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: 'dontInvert'
    });

    if (code && code.data) {
        let token = extractToken(code.data);
        if (token && token !== lastToken) {
            lastToken  = token;
            processing = true;

            // Flash feedback
            const flash = document.getElementById('scan-flash');
            flash.classList.remove('hidden');
            flash.classList.add('flex');
            setTimeout(() => {
                flash.classList.add('hidden');
                flash.classList.remove('flex');
            }, 800);

            stopCamera();
            verifyToken(token);
        }
    }
}

function extractToken(raw) {
    const urlMatch = raw.match(/\/verify\/([a-f0-9]+)/i);
    if (urlMatch) return urlMatch[1];
    if (/^[a-f0-9]{32,}$/i.test(raw.trim())) return raw.trim();
    return raw.trim();
}

// ── Manual verify ────────────────────────────────────────────────────────────

function verifyManual() {
    const input = document.getElementById('manual-token').value.trim();
    if (!input) return;
    verifyToken(extractToken(input));
}

// ── API call ─────────────────────────────────────────────────────────────────

async function verifyToken(token) {
    document.getElementById('idle-panel').classList.add('hidden');
    document.getElementById('result-panel').classList.remove('hidden');
    document.getElementById('result-loading').classList.remove('hidden');
    document.getElementById('result-loading').classList.add('flex');
    document.getElementById('result-success').classList.add('hidden');
    document.getElementById('result-error').classList.add('hidden');

    try {
        const res = await fetch('{{ route("admin.bookings.qr-verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept':       'application/json',
            },
            body: JSON.stringify({ token }),
        });

        const data = await res.json();

        document.getElementById('result-loading').classList.add('hidden');
        document.getElementById('result-loading').classList.remove('flex');

        if (data.valid && data.booking) {
            showSuccess(data.booking);
        } else {
            showError(data.message || 'QR code not recognized or booking not found.');
        }
    } catch (e) {
        document.getElementById('result-loading').classList.add('hidden');
        document.getElementById('result-loading').classList.remove('flex');
        showError('Network error. Please check your connection and try again.');
    }
}

function showSuccess(b) {
    // Avatar initial
    document.getElementById('r-avatar').textContent = (b.parishioner || '?')[0].toUpperCase();

    document.getElementById('r-name').textContent    = b.parishioner || '';
    document.getElementById('r-contact').textContent = b.contact     || '';
    document.getElementById('r-type').textContent    = b.type        || '';
    document.getElementById('r-ref').textContent     = b.reference   || '';
    document.getElementById('r-date').textContent    = b.scheduled_date || '';
    document.getElementById('r-time').textContent    = b.scheduled_time || '';
    document.getElementById('r-fee').textContent     = b.service_fee > 0
        ? '₱' + parseFloat(b.service_fee).toLocaleString('en-PH', { minimumFractionDigits: 2 })
        : 'Free';
    document.getElementById('r-link').href = b.url || '#';

    const statusColors = {
        pending:   'bg-amber-100 text-amber-800',
        confirmed: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    const statusEl = document.getElementById('r-status');
    statusEl.textContent = b.status_label || b.status;
    statusEl.className   = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold '
                         + (statusColors[b.status] || 'bg-gray-100 text-gray-800');

    const payColors = {
        paid:    'bg-green-100 text-green-800',
        pending: 'bg-amber-100 text-amber-800',
        unpaid:  'bg-gray-100 text-gray-600',
        failed:  'bg-red-100 text-red-700',
    };
    const payEl = document.getElementById('r-payment');
    const payStatus = b.payment_status || 'unpaid';
    payEl.textContent = payStatus.charAt(0).toUpperCase() + payStatus.slice(1);
    payEl.className   = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold '
                      + (payColors[payStatus] || 'bg-gray-100 text-gray-600');

    document.getElementById('result-success').classList.remove('hidden');
}

function showError(msg) {
    document.getElementById('error-msg').textContent = msg;
    document.getElementById('result-error').classList.remove('hidden');
}

function resetScanner() {
    lastToken  = null;
    processing = false;
    document.getElementById('result-panel').classList.add('hidden');
    document.getElementById('result-success').classList.add('hidden');
    document.getElementById('result-error').classList.add('hidden');
    document.getElementById('idle-panel').classList.remove('hidden');
    document.getElementById('manual-token').value = '';
}

// Stop camera when leaving the page
window.addEventListener('beforeunload', stopCamera);
</script>
@endpush
