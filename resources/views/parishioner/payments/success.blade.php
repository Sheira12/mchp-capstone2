@extends('layouts.portal')
@section('title', 'Payment Processing')

@section('content')
<div class="py-12 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center max-w-md w-full">

        {{-- Spinner while waiting for webhook --}}
        <div id="pending-state">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Verifying Payment...</h2>
            <p class="text-gray-500 mb-2">Please wait while we confirm your payment.</p>
            <p class="text-sm text-gray-400 mb-6">Do not close this page.</p>
            @if($ref)
                <p class="text-xs text-gray-400">Reference: <span class="font-mono">{{ $ref }}</span></p>
            @endif
        </div>

        {{-- Shown after webhook confirms payment --}}
        <div id="success-state" style="display:none;">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Confirmed!</h2>
            <p class="text-gray-500 mb-6">Your payment has been verified. Redirecting to your receipt...</p>
        </div>

        {{-- Shown if payment takes too long --}}
        <div id="delayed-state" style="display:none;">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12A9 9 0 113 12a9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Payment Processing</h2>
            <p class="text-gray-500 mb-4">Payment received — our system is finalizing the record.</p>
            @if($ref)
                <p class="text-sm text-gray-400 mb-6">Reference: <span class="font-mono font-medium">{{ $ref }}</span></p>
            @endif
            <div class="flex flex-col gap-3">
                <a href="{{ route('parishioner.payments.index') }}" class="btn-primary">View Payment History</a>
                <a href="{{ route('parishioner.dashboard') }}" class="btn-secondary">Back to Dashboard</a>
            </div>
        </div>

    </div>
</div>

@if($ref)
<script>
(function() {
    const ref        = @json($ref);
    const pollUrl    = @json(route('parishioner.payments.check-status'));
    let   attempts   = 0;
    const maxAttempts = 20;   // 20 × 3s = 60 seconds max
    const interval   = 3000;  // poll every 3 seconds

    function poll() {
        attempts++;
        fetch(pollUrl + '?ref=' + encodeURIComponent(ref), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'paid' && data.receipt_url) {
                document.getElementById('pending-state').style.display = 'none';
                document.getElementById('success-state').style.display = 'block';
                setTimeout(() => { window.location.href = data.receipt_url; }, 1500);
                return;
            }
            if (attempts < maxAttempts) {
                setTimeout(poll, interval);
            } else {
                // Timeout — show delayed state so user can navigate manually
                document.getElementById('pending-state').style.display = 'none';
                document.getElementById('delayed-state').style.display = 'block';
            }
        })
        .catch(() => {
            if (attempts < maxAttempts) setTimeout(poll, interval);
        });
    }

    // Start polling after 2 seconds (give webhook time to arrive)
    setTimeout(poll, 2000);
})();
</script>
@endif
@endsection
