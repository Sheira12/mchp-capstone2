<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Identity — {{ config('parish.name', 'MHC Parish') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .auth-bg { background-image: url('/images/church-bg.jpg'); background-size: cover; background-position: center; background-attachment: fixed; }
        .auth-overlay { background: linear-gradient(135deg, rgba(15,23,80,0.82) 0%, rgba(30,58,138,0.78) 50%, rgba(49,46,129,0.82) 100%); }
        .otp-input {
            width: 52px; height: 60px;
            text-align: center; font-size: 1.75rem; font-weight: 800;
            border: 2px solid #e2e8f0; border-radius: 0.75rem;
            color: #0f172a; background: #fff;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }
        .otp-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
        .otp-input.filled { border-color: #2563eb; background: #eff6ff; }
        #countdown { font-weight: 700; color: #2563eb; }
    </style>
</head>
<body class="min-h-screen auth-bg">
<div class="min-h-screen auth-overlay flex flex-col items-center justify-center px-4">

    <div class="w-full max-w-md">

        {{-- Header --}}
        <div class="text-center mb-6">
            <img src="{{ asset('images/parish-logo.png') }}" alt="Logo"
                 class="w-16 h-16 rounded-full mx-auto mb-3 border-2 border-white/50 object-cover"
                 onerror="this.style.display='none'">
            <h1 class="text-xl font-bold text-white">{{ config('parish.name', 'Mary Help of Christians Parish') }}</h1>
            <p class="text-blue-200 text-sm">{{ config('parish.address', 'Southville 1, Niugan, Cabuyao, Laguna') }}</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">

            <div class="text-center mb-6">
                <div class="w-14 h-14 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Two-Factor Verification</h2>
                <p class="text-sm text-gray-500 mt-1">A 6-digit verification code was sent to:</p>
                <div class="mt-2 bg-blue-50 border border-blue-200 rounded-xl px-4 py-2 inline-block">
                    <span class="font-bold text-blue-800 text-base tracking-wide">{{ $maskedEmail }}</span>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    Check your <strong>Primary</strong> inbox. If not found, check <strong>Spam/Promotions</strong>.
                </p>
            </div>

            {{-- Fallback: show code on screen when email fails --}}
            @if(!empty($devCode) && config('app.debug'))
            <div class="mb-5 bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4 text-center">
                <p class="text-xs font-bold text-yellow-800 uppercase tracking-wider mb-2">
                    ⚠ Email delivery failed — use this code:
                </p>
                <p class="text-4xl font-black text-blue-900 tracking-[0.35em] font-mono">{{ $devCode }}</p>
            </div>
            @endif

            {{-- Success / error alerts --}}
            @if(session('resent'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                {{ session('resent') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
            @endif

            {{-- OTP Form --}}
            <form method="POST" action="{{ route('2fa.verify') }}" id="otp-form">
                @csrf
                <input type="hidden" name="code" id="otp-hidden">

                <div class="flex justify-center gap-2 mb-4" id="otp-boxes">
                    @for($i = 0; $i < 6; $i++)
                    <input type="text" inputmode="numeric" maxlength="1"
                           class="otp-input" autocomplete="off">
                    @endfor
                </div>

                <p class="text-center text-sm text-gray-500 mb-5">
                    Code expires in <span id="countdown">15:00</span>
                </p>

                <button type="submit" id="submit-btn"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition text-base">
                    Verify & Sign In
                </button>
            </form>

            {{-- Actions --}}
            <div class="text-center mt-5 space-y-3">
                <a href="https://mail.google.com" target="_blank"
                   class="w-full flex items-center justify-center gap-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 font-semibold text-sm py-2.5 rounded-xl transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/>
                    </svg>
                    Open Gmail to get your code →
                </a>

                <p class="text-sm text-gray-500">Didn't receive the code?</p>
                <form method="POST" action="{{ route('2fa.resend') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-semibold text-blue-600 hover:underline">
                        Resend Code
                    </button>
                </form>
            </div>

            <p class="text-center text-sm text-gray-400 mt-4">
                <a href="{{ route('login') }}" class="hover:underline">← Back to Sign In</a>
            </p>
        </div>

        <p class="text-center text-xs text-white/50 mt-4 pb-4">
            Mary Help of Christians Parish · Southville 1, Niugan, Cabuyao, Laguna
        </p>
    </div>
</div>

<script>
const boxes   = document.querySelectorAll('.otp-input');
const hidden  = document.getElementById('otp-hidden');
const form    = document.getElementById('otp-form');

boxes.forEach((box, i) => {
    box.addEventListener('input', e => {
        e.target.value = e.target.value.replace(/\D/g, '').slice(-1);
        e.target.classList.toggle('filled', e.target.value.length > 0);
        updateHidden();
        if (e.target.value && i < 5) boxes[i + 1].focus();
        if (getCode().length === 6) form.submit();
    });
    box.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !e.target.value && i > 0) {
            boxes[i - 1].value = '';
            boxes[i - 1].classList.remove('filled');
            boxes[i - 1].focus();
            updateHidden();
        }
    });
    box.addEventListener('paste', e => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
        pasted.split('').forEach((ch, j) => { if (boxes[j]) { boxes[j].value = ch; boxes[j].classList.add('filled'); } });
        updateHidden();
        if (pasted.length === 6) form.submit();
        else boxes[Math.min(pasted.length, 5)].focus();
    });
});

function getCode()    { return Array.from(boxes).map(b => b.value).join(''); }
function updateHidden() { hidden.value = getCode(); }

boxes[0]?.focus();

// Countdown
let seconds = 15 * 60;
const countEl = document.getElementById('countdown');
function tick() {
    const m = Math.floor(seconds / 60), s = seconds % 60;
    countEl.textContent = m + ':' + String(s).padStart(2, '0');
    if (seconds <= 0) {
        countEl.textContent = 'Expired';
        document.getElementById('submit-btn').disabled = true;
        document.getElementById('submit-btn').style.opacity = '0.5';
        clearInterval(timer);
    }
    seconds--;
}
tick();
const timer = setInterval(tick, 1000);
</script>
</body>
</html>
