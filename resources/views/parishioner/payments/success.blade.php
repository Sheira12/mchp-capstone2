@extends('layouts.portal')
@section('title', 'Payment Processing')

@section('content')
<div class="min-h-[60vh] py-10 flex items-center justify-center px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sm:p-10 text-center w-full max-w-md">

        {{-- ── STATE 1: Spinner — waiting for PayMongo webhook to fire ── --}}
        <div id="pending-state">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Verifying Payment...</h2>
            <p class="text-gray-500 mb-2">Please wait while we confirm your payment with PayMongo.</p>
            <p class="text-sm text-gray-400 mb-4">Do not close this page.</p>
            @if($ref)
                <p class="text-xs text-gray-400">Reference: <span class="font-mono">{{ $ref }}</span></p>
            @endif
            <p id="attempt-counter" class="text-xs text-gray-300 mt-2"></p>
        </div>

        {{-- ── STATE 2: PayMongo confirmed — waiting for admin approval ── --}}
        <div id="verification-state" style="display:none;">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Payment Received!</h2>
            <p class="text-gray-600 mb-2">
                Your payment has been received and is being reviewed by the parish office.
            </p>
            <p class="text-sm text-gray-500 mb-4">
                You will receive an email once your payment is approved.
                This usually takes a few minutes during office hours.
            </p>
            @if($ref)
                <p class="text-xs text-gray-400 mb-6">Reference: <span class="font-mono font-medium">{{ $ref }}</span></p>
            @endif
            <div class="flex flex-col gap-3">
                <a href="{{ route('parishioner.payments.index') }}"
                   class="w-full py-3 rounded-xl font-bold text-white text-sm"
                   style="background:#007DFE;">
                    View Payment History
                </a>
                <a href="{{ route('parishioner.dashboard') }}"
                   class="w-full py-3 rounded-xl font-bold text-gray-700 text-sm border-2 border-gray-200">
                    Back to Dashboard
                </a>
            </div>
            <p class="text-xs text-gray-400 mt-4">This page will automatically update when approved.</p>
        </div>

        {{-- ── STATE 3: Admin approved — redirect to receipt ── --}}
        <div id="success-state" style="display:none;">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Confirmed!</h2>
            <p class="text-gray-500 mb-6">Your payment has been verified and approved. Redirecting to your receipt...</p>
        </div>

        {{-- ── STATE 4: Failed / rejected ── --}}
        <div id="failed-state" style="display:none;">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Payment Not Completed</h2>
            <p class="text-gray-500 mb-2" id="failed-message">The payment could not be completed.</p>
            @if($ref)
                <p class="text-xs text-gray-400 mb-6">Reference: <span class="font-mono">{{ $ref }}</span></p>
            @endif
            <div class="flex flex-col gap-3">
                <a href="{{ $payment && $payment->booking_id ? route('parishioner.payments.pay', $payment->booking_id) : route('parishioner.bookings.index') }}"
                   class="w-full py-3 rounded-xl font-bold text-white text-sm text-center"
                   style="background:#007DFE;">
                    Try Again
                </a>
                <a href="{{ route('parishioner.bookings.index') }}"
                   class="w-full py-3 rounded-xl font-bold text-gray-700 text-sm border-2 border-gray-200 text-center">
                    View My Bookings
                </a>
            </div>
        </div>

        {{-- ── STATE 5: Timeout — PayMongo/webhook taking too long ── --}}
        <div id="delayed-state" style="display:none;">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Still Processing...</h2>
            <p class="text-gray-500 mb-2">
                Your payment is being confirmed. This is taking a little longer than usual.
            </p>
            <p class="text-sm text-gray-500 mb-4">
                You do <strong>not</strong> need to pay again.
                Check your Payment History in a few minutes.
            </p>
            @if($ref)
                <p class="text-xs text-gray-400 mb-5">Reference: <span class="font-mono font-medium">{{ $ref }}</span></p>
            @endif
            <div class="flex flex-col gap-3">
                <button onclick="restartPolling()"
                        class="w-full py-3 rounded-xl font-bold text-white text-sm"
                        style="background:#007DFE;">
                    Check Again
                </button>
                <a href="{{ route('parishioner.payments.index') }}"
                   class="w-full py-3 rounded-xl font-bold text-gray-700 text-sm border-2 border-gray-200">
                    View Payment History
                </a>
                <a href="{{ route('parishioner.dashboard') }}"
                   class="w-full py-3 rounded-xl font-bold text-gray-500 text-sm">
                    Back to Dashboard
                </a>
            </div>
        </div>

    </div>
</div>

@if($ref)
<script>
(function () {
    const ref         = @json($ref);
    const pollUrl     = @json(route('parishioner.payments.check-status'));

    // Poll every 4 seconds.
    // First 15 attempts (60 s): waiting for PayMongo webhook.
    // Attempts 16–40 (another 100 s): admin has likely been notified; show verification state.
    // After 40 attempts: show "still processing" fallback.
    const MAX_ATTEMPTS  = 40;
    const POLL_INTERVAL = 4000;

    let attempts = 0;
    let timer    = null;
    let stopped  = false;

    // Track whether we've already switched to verification state
    // so we don't flicker back to the spinner
    let inVerificationState = false;

    function showState(name) {
        ['pending', 'verification', 'success', 'failed', 'delayed'].forEach(function (s) {
            const el = document.getElementById(s + '-state');
            if (el) el.style.display = (s === name) ? 'block' : 'none';
        });
    }

    function updateCounter() {
        const el = document.getElementById('attempt-counter');
        if (el) {
            if (attempts > 0 && !inVerificationState) {
                el.textContent = 'Checking... (' + attempts + '/' + MAX_ATTEMPTS + ')';
            } else {
                el.textContent = '';
            }
        }
    }

    function poll() {
        if (stopped) return;
        attempts++;
        updateCounter();

        fetch(pollUrl + '?ref=' + encodeURIComponent(ref), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (stopped) return;

            var status = data.status || 'pending';

            // Admin approved — show success and redirect to receipt
            if (status === 'paid' && data.receipt_url) {
                stopped = true;
                showState('success');
                setTimeout(function () {
                    window.location.href = data.receipt_url;
                }, 1500);
                return;
            }

            // PayMongo confirmed but admin hasn't approved yet
            if (status === 'pending_verification') {
                if (!inVerificationState) {
                    inVerificationState = true;
                    showState('verification');
                }
                // Keep polling slowly so we detect when admin approves
                if (attempts < MAX_ATTEMPTS) {
                    timer = setTimeout(poll, POLL_INTERVAL);
                } else {
                    showState('delayed');
                }
                return;
            }

            // Terminal failure
            if (status === 'failed' || status === 'cancelled' || status === 'expired') {
                stopped = true;
                var msgEl = document.getElementById('failed-message');
                if (msgEl) {
                    if (status === 'expired') {
                        msgEl.textContent = 'The payment session expired. Please try again.';
                    } else if (status === 'cancelled') {
                        msgEl.textContent = 'The payment was cancelled.';
                    } else {
                        msgEl.textContent = 'The payment was not approved. Please contact the parish office or try again.';
                    }
                }
                showState('failed');
                return;
            }

            // Still pending — keep polling
            if (attempts < MAX_ATTEMPTS) {
                timer = setTimeout(poll, POLL_INTERVAL);
            } else {
                showState('delayed');
            }
        })
        .catch(function () {
            // Network error — keep trying
            if (!stopped && attempts < MAX_ATTEMPTS) {
                timer = setTimeout(poll, POLL_INTERVAL);
            } else if (!stopped) {
                showState('delayed');
            }
        });
    }

    window.restartPolling = function () {
        stopped              = false;
        attempts             = 0;
        inVerificationState  = false;
        if (timer) clearTimeout(timer);
        showState('pending');
        timer = setTimeout(poll, 500);
    };

    // Wait 2 seconds before first poll to give webhook time to arrive
    timer = setTimeout(poll, 2000);
})();
</script>
@endif
@endsection
