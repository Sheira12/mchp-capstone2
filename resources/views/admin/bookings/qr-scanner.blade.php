@extends('layouts.app')
@section('title', 'QR Scanner — Walk-in Verification')
@section('page-title', 'QR Code Scanner')

@section('content')
<div class="py-6 max-w-2xl space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Walk-in QR Verification</h1>
            <p class="text-sm text-gray-500 mt-0.5">Scan a parishioner's booking QR code to verify and look up their booking.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}"
           class="text-sm text-blue-600 hover:underline flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            All Bookings
        </a>
    </div>

    {{-- Scanner Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-white">Camera QR Scanner</p>
                <p class="text-blue-200 text-xs">Point camera at the parishioner's QR code</p>
            </div>
        </div>

        <div class="p-6">
            {{-- Camera view --}}
            <div class="relative rounded-xl overflow-hidden bg-gray-900 mb-4" style="aspect-ratio:4/3;max-height:320px;">
                <video id="qr-video" class="w-full h-full object-cover" playsinline></video>
                {{-- Scan overlay --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="relative w-48 h-48">
                        {{-- Corner brackets --}}
                        <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-400 rounded-tl-lg"></div>
                        <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-400 rounded-tr-lg"></div>
                        <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-400 rounded-bl-lg"></div>
                        <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-400 rounded-br-lg"></div>
                        {{-- Scan line animation --}}
                        <div id="scan-line" class="absolute left-2 right-2 h-0.5 bg-blue-400 opacity-80" style="top:50%;animation:scanline 2s ease-in-out infinite;"></div>
                    </div>
                </div>
                {{-- Status overlay --}}
                <div id="camera-status" class="absolute inset-0 flex items-center justify-center bg-gray-900/80">
                    <div class="text-center text-white">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                        <p class="text-sm font-medium">Camera not started</p>
                        <p class="text-xs text-gray-400 mt-1">Click Start Camera below</p>
                    </div>
                </div>
            </div>

            {{-- Controls --}}
            <div class="flex gap-3 mb-5">
                <button id="start-btn" onclick="startCamera()"
                        class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-2.5 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Start Camera
                </button>
                <button id="stop-btn" onclick="stopCamera()" disabled
                        class="flex-1 flex items-center justify-center gap-2 bg-gray-200 text-gray-500 font-bold text-sm py-2.5 rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                    Stop Camera
                </button>
            </div>

            {{-- Manual token input --}}
            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Or enter token manually</p>
                <div class="flex gap-2">
                    <input id="manual-token" type="text" placeholder="Paste QR token here..."
                           class="flex-1 text-sm border border-gray-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-400 font-mono">
                    <button onclick="verifyManual()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm px-4 rounded-xl transition">
                        Verify
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Result Panel --}}
    <div id="result-panel" class="hidden">
        {{-- Success --}}
        <div id="result-success" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="font-bold text-white text-lg">QR Code Verified ✓</p>
                    <p class="text-green-100 text-xs">Booking found and authenticated</p>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Parishioner</p>
                        <p id="r-name" class="font-bold text-gray-900 text-base"></p>
                        <p id="r-contact" class="text-xs text-gray-500"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Service</p>
                        <p id="r-type" class="font-bold text-gray-900"></p>
                        <p id="r-ref" class="text-xs font-mono text-gray-400"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Scheduled</p>
                        <p id="r-date" class="font-semibold text-gray-800"></p>
                        <p id="r-time" class="text-xs text-gray-500"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Status</p>
                        <span id="r-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"></span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Service Fee</p>
                        <p id="r-fee" class="font-semibold text-gray-800"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Payment</p>
                        <span id="r-payment" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"></span>
                    </div>
                </div>
                <div class="flex gap-3 pt-2 border-t border-gray-100">
                    <a id="r-link" href="#"
                       class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-2.5 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        View Full Booking
                    </a>
                    <button onclick="resetScanner()"
                            class="flex-1 flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm py-2.5 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Scan Another
                    </button>
                </div>
            </div>
        </div>

        {{-- Error --}}
        <div id="result-error" class="hidden bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="font-bold text-white">Verification Failed</p>
                    <p id="error-msg" class="text-red-100 text-xs"></p>
                </div>
            </div>
            <div class="p-4 flex justify-center">
                <button onclick="resetScanner()"
                        class="flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-700 font-bold text-sm py-2.5 px-6 rounded-xl transition border border-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Try Again
                </button>
            </div>
        </div>
    </div>

    {{-- Recent scans --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-3 text-sm">Recent Bookings (Today)</h3>
        @php
            $todayBookings = \App\Models\Booking::with('parishioner')
                ->whereDate('scheduled_date', today())
                ->whereIn('status', ['pending','confirmed'])
                ->orderBy('scheduled_time')
                ->take(8)
                ->get();
        @endphp
        @if($todayBookings->isEmpty())
            <p class="text-sm text-gray-400 text-center py-3">No bookings scheduled for today.</p>
        @else
        <div class="space-y-2">
            @foreach($todayBookings as $b)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                        {{ substr($b->parishioner->first_name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $b->parishioner->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $b->getTypeLabel() }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-mono text-gray-400">{{ $b->scheduled_time ? \Carbon\Carbon::parse($b->scheduled_time)->format('g:i A') : 'TBD' }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $b->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $b->getStatusLabel() }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
{{-- jsQR — lightweight QR decoder, no server needed --}}
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<style>
@keyframes scanline {
    0%   { top: 10%; }
    50%  { top: 90%; }
    100% { top: 10%; }
}
</style>
<script>
let videoStream = null;
let scanInterval = null;
let scanning = false;
let lastToken = null;

const video    = document.getElementById('qr-video');
const canvas   = document.createElement('canvas');
const ctx      = canvas.getContext('2d');
const statusEl = document.getElementById('camera-status');

async function startCamera() {
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        video.srcObject = videoStream;
        await video.play();
        statusEl.classList.add('hidden');
        document.getElementById('start-btn').disabled = true;
        document.getElementById('stop-btn').disabled  = false;
        document.getElementById('stop-btn').classList.remove('bg-gray-200','text-gray-500');
        document.getElementById('stop-btn').classList.add('bg-red-100','text-red-700');
        scanning = true;
        scanInterval = setInterval(scanFrame, 200);
    } catch (err) {
        statusEl.innerHTML = `<div class="text-center text-white"><p class="text-sm font-medium text-red-300">Camera access denied</p><p class="text-xs text-gray-400 mt-1">${err.message}</p></div>`;
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
    statusEl.classList.remove('hidden');
    statusEl.innerHTML = `<div class="text-center text-white"><svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/></svg><p class="text-sm font-medium">Camera stopped</p></div>`;
    document.getElementById('start-btn').disabled = false;
    document.getElementById('stop-btn').disabled  = true;
    document.getElementById('stop-btn').classList.add('bg-gray-200','text-gray-500');
    document.getElementById('stop-btn').classList.remove('bg-red-100','text-red-700');
}

function scanFrame() {
    if (!scanning || video.readyState !== video.HAVE_ENOUGH_DATA) return;
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });
    if (code && code.data) {
        // Extract token from URL or use raw value
        let token = code.data;
        const match = token.match(/\/verify\/([a-f0-9]+)/i);
        if (match) token = match[1];
        if (token !== lastToken) {
            lastToken = token;
            stopCamera();
            verifyToken(token);
        }
    }
}

function verifyManual() {
    const token = document.getElementById('manual-token').value.trim();
    if (!token) return;
    // Extract token from URL if pasted
    const match = token.match(/\/verify\/([a-f0-9]+)/i);
    verifyToken(match ? match[1] : token);
}

async function verifyToken(token) {
    document.getElementById('result-panel').classList.remove('hidden');
    document.getElementById('result-success').classList.add('hidden');
    document.getElementById('result-error').classList.add('hidden');

    try {
        const res = await fetch('{{ route("admin.bookings.qr-verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ token }),
        });

        const data = await res.json();

        if (data.valid && data.booking) {
            const b = data.booking;
            document.getElementById('r-name').textContent    = b.parishioner;
            document.getElementById('r-contact').textContent = b.contact || '';
            document.getElementById('r-type').textContent    = b.type;
            document.getElementById('r-ref').textContent     = b.reference;
            document.getElementById('r-date').textContent    = b.scheduled_date;
            document.getElementById('r-time').textContent    = b.scheduled_time;
            document.getElementById('r-fee').textContent     = b.service_fee > 0 ? '₱' + parseFloat(b.service_fee).toLocaleString('en-PH', {minimumFractionDigits:2}) : 'Free';
            document.getElementById('r-link').href           = b.url;

            // Status badge
            const statusColors = { pending:'bg-amber-100 text-amber-800', confirmed:'bg-green-100 text-green-800', completed:'bg-blue-100 text-blue-800', cancelled:'bg-red-100 text-red-800' };
            const statusEl = document.getElementById('r-status');
            statusEl.textContent  = b.status_label;
            statusEl.className    = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ' + (statusColors[b.status] || 'bg-gray-100 text-gray-800');

            // Payment badge
            const payColors = { paid:'bg-green-100 text-green-800', pending:'bg-amber-100 text-amber-800', unpaid:'bg-gray-100 text-gray-600' };
            const payEl = document.getElementById('r-payment');
            payEl.textContent = b.payment_status.charAt(0).toUpperCase() + b.payment_status.slice(1);
            payEl.className   = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ' + (payColors[b.payment_status] || 'bg-gray-100 text-gray-600');

            document.getElementById('result-success').classList.remove('hidden');
        } else {
            document.getElementById('error-msg').textContent = data.message || 'QR code not recognized.';
            document.getElementById('result-error').classList.remove('hidden');
        }
    } catch (e) {
        document.getElementById('error-msg').textContent = 'Network error. Please try again.';
        document.getElementById('result-error').classList.remove('hidden');
    }
}

function resetScanner() {
    lastToken = null;
    document.getElementById('result-panel').classList.add('hidden');
    document.getElementById('result-success').classList.add('hidden');
    document.getElementById('result-error').classList.add('hidden');
    document.getElementById('manual-token').value = '';
}
</script>
@endpush
