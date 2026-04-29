@extends('layouts.portal')

@section('title', 'Booking Details')

@section('content')
<div class="space-y-6 max-w-2xl">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('parishioner.bookings.index') }}"
               class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-xl font-bold text-gray-900">Booking Details</h1>
        </div>
        @php $statusColors = ['pending'=>'amber','confirmed'=>'green','completed'=>'blue','cancelled'=>'red']; @endphp
        <span class="badge badge-{{ $booking->status }} text-sm px-3 py-1">{{ $booking->getStatusLabel() }}</span>
    </div>

    {{-- Main Details Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        {{-- Status color bar --}}
        <div class="h-1.5 bg-{{ $statusColors[$booking->status] ?? 'gray' }}-400"></div>

        <div class="p-6">
            <div class="flex items-start gap-4 mb-6">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $booking->getTypeLabel() }}</h2>
                    <p class="text-sm text-gray-500 font-mono mt-0.5">{{ $booking->reference_number }}</p>
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 rounded-xl p-4">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Date</dt>
                    <dd class="font-bold text-gray-900">{{ $booking->scheduled_date->format('F d, Y') }}</dd>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Time</dt>
                    <dd class="font-bold text-gray-900">
                        {{ $booking->scheduled_time ? \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') : 'To be confirmed' }}
                    </dd>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Service Fee</dt>
                    <dd class="font-bold text-gray-900">
                        {{ $booking->service_fee > 0 ? '₱' . number_format($booking->service_fee, 2) : 'Free / Donation' }}
                    </dd>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Submitted</dt>
                    <dd class="font-bold text-gray-900">{{ $booking->created_at->format('M d, Y') }}</dd>
                </div>
                @if($booking->address)
                <div class="col-span-2 bg-gray-50 rounded-xl p-4">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Address</dt>
                    <dd class="font-bold text-gray-900">{{ $booking->address }}</dd>
                </div>
                @endif
                @if($booking->notes)
                <div class="col-span-2 bg-gray-50 rounded-xl p-4">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Your Notes</dt>
                    <dd class="text-gray-700">{{ $booking->notes }}</dd>
                </div>
                @endif
            </dl>

            @if($booking->admin_notes)
            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-xs font-bold text-blue-600 uppercase tracking-wide mb-1">Parish Office Notes</p>
                <p class="text-sm text-blue-800">{{ $booking->admin_notes }}</p>
            </div>
            @endif

            @if($booking->cancellation_reason)
            <div class="mt-4 bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-xs font-bold text-red-600 uppercase tracking-wide mb-1">Cancellation Reason</p>
                <p class="text-sm text-red-800">{{ $booking->cancellation_reason }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment Section --}}
    @if($booking->service_fee > 0)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Payment
        </h2>

        @if($booking->payment && $booking->payment->status === 'paid')
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl p-4">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <p class="font-bold text-green-800">Payment Confirmed</p>
                <p class="text-sm text-green-700">₱{{ number_format($booking->payment->amount, 2) }} via {{ ucfirst($booking->payment->payment_method) }}</p>
                @if($booking->payment->receipt_number)
                <p class="text-xs text-green-600 font-mono mt-0.5">Receipt: {{ $booking->payment->receipt_number }}</p>
                @endif
            </div>
        </div>

        @elseif(in_array($booking->status, ['pending', 'confirmed']))
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
            <p class="text-sm font-semibold text-amber-800 mb-1">Payment Required</p>
            <p class="text-sm text-amber-700">Amount due: <strong>₱{{ number_format($booking->service_fee, 2) }}</strong></p>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <button onclick="initiatePayment('gcash')"
                    class="flex items-center justify-center gap-2 bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Pay via GCash
            </button>
            <button onclick="initiatePayment('paymaya')"
                    class="flex items-center justify-center gap-2 bg-green-600 text-white font-bold py-3 rounded-xl hover:bg-green-700 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Pay via Maya
            </button>
        </div>
        @else
        <p class="text-sm text-gray-400">No payment required for this booking.</p>
        @endif
    </div>
    @endif

    {{-- QR Code --}}
    @if($booking->qrCode)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
        <h2 class="font-bold text-gray-900 mb-4">Booking QR Code</h2>
        <div class="inline-block p-4 bg-white border-2 border-gray-100 rounded-2xl shadow-sm">
            @if($booking->qrCode->qr_image_path)
            <img src="{{ Storage::url($booking->qrCode->qr_image_path) }}" alt="QR Code" class="w-36 h-36">
            @endif
        </div>
        <p class="text-xs text-gray-400 mt-3">Present this QR code at the parish office for verification</p>
    </div>
    @endif

    {{-- Cancel Booking --}}
    @if($booking->status === 'pending')
    <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 mb-1">Cancel This Booking</h2>
        <p class="text-sm text-gray-500 mb-4">Only pending bookings can be cancelled. This action cannot be undone.</p>
        <form action="{{ route('parishioner.bookings.cancel', $booking) }}" method="POST"
              onsubmit="return confirm('Are you sure you want to cancel this booking?')">
            @csrf
            <div class="mb-4">
                <label class="form-label">Reason for cancellation <span class="text-red-500">*</span></label>
                <textarea name="cancellation_reason" rows="2" required
                          class="form-input w-full" placeholder="Please provide a reason..."></textarea>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-red-50 text-red-700 border border-red-200 font-semibold px-5 py-2.5 rounded-lg hover:bg-red-100 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Cancel Booking
            </button>
        </form>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function initiatePayment(method) {
    const amount = {{ $booking->service_fee }};
    const bookingId = {{ $booking->id }};
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    fetch('{{ route("parishioner.payments.initiate") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ method, amount, booking_id: bookingId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.checkout_url) {
            window.location.href = data.checkout_url;
        } else {
            alert(data.error || 'Payment initiation failed. Please try again.');
        }
    })
    .catch(() => alert('An error occurred. Please try again.'));
}
</script>
@endpush
