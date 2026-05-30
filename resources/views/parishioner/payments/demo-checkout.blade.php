<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>{{ $method === 'gcash' ? 'GCash' : 'Maya' }} — Secure Payment</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f0f2f5;
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    padding: 1rem;
}

/* ── Card shell ── */
.card {
    background: #fff;
    border-radius: 24px;
    width: 100%; max-width: 390px;
    overflow: hidden;
    box-shadow: 0 24px 64px rgba(0,0,0,0.18);
}

/* ── Brand header ── */
.brand-header {
    padding: 1.25rem 1.5rem 1rem;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid #f1f5f9;
}
.brand-logo { display: flex; align-items: center; gap: 10px; }
.brand-name { font-weight: 900; font-size: 1.25rem; }
.brand-gcash { color: #007DFE; }
.brand-maya  { color: #00B140; }
.secure-badge {
    display: flex; align-items: center; gap: 4px;
    font-size: 0.7rem; color: #6b7280; font-weight: 600;
}

/* ── Step indicator ── */
.steps {
    display: flex; align-items: center; justify-content: center;
    gap: 0; padding: 0.875rem 1.5rem;
    background: #fafafa; border-bottom: 1px solid #f1f5f9;
}
.step {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    flex: 1;
}
.step-dot {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; font-weight: 800;
    border: 2px solid #e5e7eb; color: #9ca3af; background: #fff;
    transition: all 0.3s;
}
.step-dot.active { border-color: var(--brand); color: var(--brand); background: #fff; }
.step-dot.done   { border-color: var(--brand); background: var(--brand); color: #fff; }
.step-label { font-size: 0.6rem; font-weight: 600; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.04em; }
.step-label.active { color: var(--brand); }
.step-line { flex: 1; height: 2px; background: #e5e7eb; margin: 0 4px; margin-bottom: 14px; transition: background 0.3s; }
.step-line.done { background: var(--brand); }

/* ── Merchant summary (always visible) ── */
.merchant-bar {
    display: flex; align-items: center; gap: 12px;
    padding: 0.875rem 1.5rem;
    background: #f8faff; border-bottom: 1px solid #e8f0fe;
}
.merchant-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: #1e3a8a; display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; flex-shrink: 0;
}
.merchant-name { font-weight: 700; font-size: 0.875rem; color: #111827; }
.merchant-amount { font-weight: 900; font-size: 1.125rem; color: var(--brand); }
.merchant-ref { font-size: 0.7rem; color: #6b7280; font-family: monospace; }

/* ── Step panels ── */
.panel { display: none; padding: 1.5rem; }
.panel.active { display: block; }

/* ── Step 1: Mobile number ── */
.step-title { font-size: 1rem; font-weight: 800; color: #111827; margin-bottom: 0.25rem; }
.step-sub   { font-size: 0.8rem; color: #6b7280; margin-bottom: 1.25rem; }

.phone-input-wrap {
    display: flex; align-items: center;
    border: 2px solid #e5e7eb; border-radius: 14px;
    overflow: hidden; margin-bottom: 1.25rem;
    transition: border-color 0.2s;
}
.phone-input-wrap:focus-within { border-color: var(--brand); }
.phone-prefix {
    padding: 0.875rem 0.875rem 0.875rem 1rem;
    background: #f9fafb; font-weight: 700; color: #374151;
    font-size: 0.9375rem; border-right: 2px solid #e5e7eb;
    white-space: nowrap;
}
.phone-input {
    flex: 1; padding: 0.875rem; border: none; outline: none;
    font-size: 1.125rem; font-weight: 700; color: #111827;
    letter-spacing: 2px; background: transparent;
}
.phone-input::placeholder { color: #d1d5db; font-weight: 400; letter-spacing: 0; }

/* ── Step 2: MPIN ── */
.mpin-label { font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.75rem; }
.mpin-dots {
    display: flex; gap: 1rem; justify-content: center; margin-bottom: 1.25rem;
}
.mpin-dot {
    width: 16px; height: 16px; border-radius: 50%;
    border: 2.5px solid #d1d5db; background: transparent;
    transition: all 0.15s;
}
.mpin-dot.filled { background: var(--brand); border-color: var(--brand); }

/* ── Keypad ── */
.keypad {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 0.625rem; margin-bottom: 1.25rem;
}
.key {
    padding: 1rem; border: none; border-radius: 14px;
    background: #f3f4f6; font-size: 1.375rem; font-weight: 700;
    color: #111827; cursor: pointer; transition: all 0.1s;
    text-align: center; user-select: none;
}
.key:hover  { background: #e5e7eb; }
.key:active { transform: scale(0.92); background: #d1d5db; }
.key.del    { font-size: 1.125rem; color: #6b7280; }
.key.empty  { background: transparent; cursor: default; pointer-events: none; }

/* ── Step 3: Review ── */
.review-card {
    background: #f8faff; border: 1.5px solid #e8f0fe;
    border-radius: 16px; padding: 1.25rem; margin-bottom: 1.25rem;
}
.review-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.5rem 0; border-bottom: 1px solid #e8f0fe;
}
.review-row:last-child { border-bottom: none; }
.review-label { font-size: 0.8rem; color: #6b7280; }
.review-value { font-size: 0.875rem; font-weight: 700; color: #111827; text-align: right; }
.review-total .review-label { font-weight: 700; color: #111827; font-size: 0.9rem; }
.review-total .review-value { font-size: 1.25rem; font-weight: 900; color: var(--brand); }

/* ── Primary button ── */
.btn-primary {
    width: 100%; padding: 1rem; border: none; border-radius: 14px;
    background: var(--brand); color: #fff;
    font-size: 1rem; font-weight: 800; cursor: pointer;
    transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-primary:hover   { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,0.2); }
.btn-primary:active  { transform: translateY(0); }
.btn-primary:disabled { opacity: 0.45; cursor: not-allowed; transform: none; box-shadow: none; }

.btn-back {
    width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 14px;
    background: transparent; color: #6b7280; font-size: 0.875rem; font-weight: 600;
    cursor: pointer; margin-top: 0.625rem; transition: all 0.15s;
}
.btn-back:hover { border-color: #9ca3af; color: #374151; }

/* ── Processing overlay ── */
.overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.65); z-index: 100;
    align-items: center; justify-content: center; flex-direction: column; gap: 1rem;
}
.overlay.show { display: flex; }
.spinner {
    width: 60px; height: 60px;
    border: 5px solid rgba(255,255,255,0.25);
    border-top-color: #fff; border-radius: 50%;
    animation: spin 0.75s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.overlay p { color: #fff; font-weight: 700; font-size: 1rem; }

/* ── Success overlay ── */
.success-overlay {
    display: none; position: fixed; inset: 0;
    background: var(--brand); z-index: 200;
    align-items: center; justify-content: center;
    flex-direction: column; gap: 1rem; text-align: center; padding: 2rem;
}
.success-overlay.show { display: flex; }
.check-anim {
    width: 88px; height: 88px; border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    animation: pop 0.5s cubic-bezier(0.175,0.885,0.32,1.275);
}
@keyframes pop { 0%{transform:scale(0)} 80%{transform:scale(1.12)} 100%{transform:scale(1)} }
.success-overlay h2 { color: #fff; font-size: 1.625rem; font-weight: 900; }
.success-overlay .amount-big { color: #fff; font-size: 2.25rem; font-weight: 900; }
.success-overlay p { color: rgba(255,255,255,0.85); font-size: 0.875rem; }

/* ── Footer ── */
.card-footer {
    padding: 0.75rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    background: #fafafa;
}
.card-footer span { font-size: 0.7rem; color: #9ca3af; }

/* ── Demo badge ── */
.demo-notice {
    margin: 0 1.5rem 1rem;
    background: #fef9c3; border: 1px solid #fde047;
    border-radius: 10px; padding: 6px 12px;
    font-size: 0.7rem; font-weight: 700; color: #854d0e;
    text-align: center;
}
</style>
</head>
<body>
@php
$brandColor = $method === 'gcash' ? '#007DFE' : '#00B140';
$brandName  = $method === 'gcash' ? 'GCash' : 'Maya';
$parishName = config('parish.name', 'MHC Parish');
$amount     = number_format($booking->service_fee, 2);
$mobileNum  = $booking->parishioner->contact_number ?? '09XXXXXXXXX';
@endphp

<style>:root { --brand: {{ $brandColor }}; }</style>

{{-- Processing overlay --}}
<div class="overlay" id="overlay-processing">
    <div class="spinner"></div>
    <p>Processing payment...</p>
</div>

{{-- Success overlay --}}
<div class="success-overlay" id="overlay-success">
    <div class="check-anim">
        <svg width="44" height="44" fill="none" stroke="white" stroke-width="3.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <h2>Payment Sent!</h2>
    <div class="amount-big">₱{{ $amount }}</div>
    <p>To <strong>{{ $parishName }}</strong></p>
    <p style="margin-top:0.5rem;font-size:0.75rem;opacity:0.7;">Redirecting to receipt...</p>
</div>

<div class="card">

    {{-- Brand header --}}
    <div class="brand-header">
        <div class="brand-logo">
            @if($method === 'gcash')
            <svg viewBox="0 0 160 44" xmlns="http://www.w3.org/2000/svg" style="height:32px;">
                <circle cx="20" cy="22" r="18" fill="none" stroke="#007DFE" stroke-width="3.5"/>
                <path d="M20 12 A10 10 0 1 0 30 22 L24 22" stroke="#007DFE" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                <line x1="24" y1="22" x2="30" y2="22" stroke="#5BB8F5" stroke-width="3.5" stroke-linecap="round"/>
                <path d="M40 15 Q45 22 40 29" stroke="#5BB8F5" stroke-width="3" fill="none" stroke-linecap="round"/>
                <path d="M46 11 Q53 22 46 33" stroke="#007DFE" stroke-width="3" fill="none" stroke-linecap="round"/>
                <text x="58" y="30" font-family="Arial,sans-serif" font-weight="900" font-size="22" fill="#0033A0">GCash</text>
            </svg>
            @else
            <svg viewBox="0 0 100 40" xmlns="http://www.w3.org/2000/svg" style="height:28px;">
                <text x="4" y="30" font-family="Arial Rounded MT Bold,Arial,sans-serif" font-weight="900" font-size="28" fill="#00B140" letter-spacing="-1">maya</text>
            </svg>
            @endif
        </div>
        <div class="secure-badge">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Secure
        </div>
    </div>

    {{-- Step indicator --}}
    <div class="steps">
        <div class="step">
            <div class="step-dot done" id="dot-1">✓</div>
            <div class="step-label active" id="lbl-1">Mobile</div>
        </div>
        <div class="step-line" id="line-1"></div>
        <div class="step">
            <div class="step-dot" id="dot-2">2</div>
            <div class="step-label" id="lbl-2">MPIN</div>
        </div>
        <div class="step-line" id="line-2"></div>
        <div class="step">
            <div class="step-dot" id="dot-3">3</div>
            <div class="step-label" id="lbl-3">Review</div>
        </div>
    </div>

    {{-- Merchant bar --}}
    <div class="merchant-bar">
        <div class="merchant-icon">⛪</div>
        <div style="flex:1;">
            <div class="merchant-name">{{ $parishName }}</div>
            <div class="merchant-ref">{{ $booking->reference_number }}</div>
        </div>
        <div class="merchant-amount">₱{{ $amount }}</div>
    </div>

    {{-- Demo notice --}}
    <div class="demo-notice">🎓 Capstone Demo — Simulated {{ $brandName }} Payment</div>

    {{-- ── STEP 1: Mobile Number ── --}}
    <div class="panel active" id="panel-1">
        <div class="step-title">Enter your {{ $brandName }} mobile number</div>
        <div class="step-sub">We'll verify your {{ $brandName }} account</div>

        <div class="phone-input-wrap">
            <div class="phone-prefix">🇵🇭 +63</div>
            <input type="tel" class="phone-input" id="phone-input"
                   placeholder="9XX XXX XXXX" maxlength="11"
                   value="{{ ltrim($mobileNum, '0') }}"
                   oninput="validatePhone()">
        </div>

        <button class="btn-primary" id="btn-next-1" onclick="goToStep(2)">
            Continue
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <a href="{{ route('parishioner.payments.pay', $booking) }}" style="display:block;text-align:center;margin-top:0.75rem;font-size:0.8rem;color:#9ca3af;text-decoration:none;">← Cancel</a>
    </div>

    {{-- ── STEP 2: MPIN ── --}}
    <div class="panel" id="panel-2">
        <div class="step-title">Enter your 4-digit MPIN</div>
        <div class="step-sub">Your {{ $brandName }} security PIN</div>

        <div class="mpin-dots">
            <div class="mpin-dot" id="mpin-1"></div>
            <div class="mpin-dot" id="mpin-2"></div>
            <div class="mpin-dot" id="mpin-3"></div>
            <div class="mpin-dot" id="mpin-4"></div>
        </div>

        <div class="keypad">
            @foreach([1,2,3,4,5,6,7,8,9,'','0','del'] as $k)
            @if($k === '')
            <div class="key empty"></div>
            @elseif($k === 'del')
            <button class="key del" onclick="mpinDel()">⌫</button>
            @else
            <button class="key" onclick="mpinAdd('{{ $k }}')">{{ $k }}</button>
            @endif
            @endforeach
        </div>

        <button class="btn-back" onclick="goToStep(1)">← Back</button>
    </div>

    {{-- ── STEP 3: Review & Pay ── --}}
    <div class="panel" id="panel-3">
        <div class="step-title">Review Payment</div>
        <div class="step-sub">Confirm the details before paying</div>

        <div class="review-card">
            <div class="review-row">
                <span class="review-label">Merchant</span>
                <span class="review-value">{{ $parishName }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Service</span>
                <span class="review-value">{{ $booking->getTypeLabel() }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Reference</span>
                <span class="review-value" style="font-family:monospace;font-size:0.8rem;">{{ $booking->reference_number }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Date</span>
                <span class="review-value">{{ $booking->scheduled_date->format('M d, Y') }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">{{ $brandName }} Account</span>
                <span class="review-value" id="review-phone">+63 9XX XXX XXXX</span>
            </div>
            <div class="review-row review-total">
                <span class="review-label">Total Amount</span>
                <span class="review-value">₱{{ $amount }}</span>
            </div>
        </div>

        <button class="btn-primary" id="btn-pay" onclick="completePay()">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Tap to Pay ₱{{ $amount }}
        </button>
        <button class="btn-back" onclick="goToStep(2)">← Back</button>
    </div>

    {{-- Footer --}}
    <div class="card-footer">
        <svg width="12" height="12" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <span>256-bit SSL Encrypted &nbsp;·&nbsp; Powered by {{ $brandName }}</span>
    </div>
</div>

{{-- Hidden form --}}
<form id="demo-form" method="POST" action="{{ route('parishioner.payments.demo-complete', [$booking, $method]) }}" style="display:none;">
    @csrf
    <input type="hidden" name="demo_reference" id="demo-ref">
</form>

<script>
let mpin = '';
const brandColor = '{{ $brandColor }}';

// ── Step navigation ──────────────────────────────────────────────────────────
function goToStep(step) {
    // Hide all panels
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + step).classList.add('active');

    // Update step dots
    for (let i = 1; i <= 3; i++) {
        const dot = document.getElementById('dot-' + i);
        const lbl = document.getElementById('lbl-' + i);
        if (i < step) {
            dot.className = 'step-dot done';
            dot.textContent = '✓';
            lbl.className = 'step-label active';
        } else if (i === step) {
            dot.className = 'step-dot active';
            dot.textContent = i;
            lbl.className = 'step-label active';
        } else {
            dot.className = 'step-dot';
            dot.textContent = i;
            lbl.className = 'step-label';
        }
    }

    // Update step lines
    for (let i = 1; i <= 2; i++) {
        const line = document.getElementById('line-' + i);
        line.className = 'step-line' + (i < step ? ' done' : '');
    }

    // If going to review, update phone display
    if (step === 3) {
        const phone = document.getElementById('phone-input').value;
        const formatted = phone.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
        document.getElementById('review-phone').textContent = '+63 ' + formatted;
    }
}

// ── Phone validation ─────────────────────────────────────────────────────────
function validatePhone() {
    const val = document.getElementById('phone-input').value.replace(/\D/g, '');
    // Enable continue if 10 digits (without leading 0) or 11 digits
    const valid = val.length >= 10;
    document.getElementById('btn-next-1').disabled = !valid;
}

// ── MPIN input ───────────────────────────────────────────────────────────────
function mpinAdd(digit) {
    if (mpin.length >= 4) return;
    mpin += digit;
    updateMpinDots();
    if (mpin.length === 4) {
        // Auto-advance to review after brief delay
        setTimeout(() => goToStep(3), 300);
    }
}

function mpinDel() {
    mpin = mpin.slice(0, -1);
    updateMpinDots();
}

function updateMpinDots() {
    for (let i = 1; i <= 4; i++) {
        const dot = document.getElementById('mpin-' + i);
        dot.classList.toggle('filled', i <= mpin.length);
    }
}

// ── Complete payment ─────────────────────────────────────────────────────────
function completePay() {
    document.getElementById('btn-pay').disabled = true;
    document.getElementById('overlay-processing').classList.add('show');

    setTimeout(() => {
        document.getElementById('overlay-processing').classList.remove('show');
        document.getElementById('overlay-success').classList.add('show');

        // Generate reference number
        const ts  = Date.now().toString().slice(-8);
        const ref = '{{ strtoupper($method) }}-' + ts;
        document.getElementById('demo-ref').value = ref;

        setTimeout(() => {
            document.getElementById('demo-form').submit();
        }, 2500);
    }, 2200);
}

// Init
validatePhone();
</script>
</body>
</html>
