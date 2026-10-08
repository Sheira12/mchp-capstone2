@extends('layouts.portal')
@section('title', 'Order of Payment')

@push('styles')
<style>
.pkg-card { background:#fff; border:2px solid #e2e8f0; border-radius:1rem; padding:1.25rem; cursor:pointer; transition:all 0.15s; position:relative; }
.pkg-card:hover { border-color:#93c5fd; background:#f0f9ff; }
.pkg-card.selected { border-color:#2563eb; background:#eff6ff; box-shadow:0 0 0 3px rgba(37,99,235,.15); }
.pkg-card input[type=radio] { position:absolute; opacity:0; }
.pkg-check { position:absolute; top:0.75rem; right:0.75rem; width:22px; height:22px; border-radius:50%; border:2px solid #e2e8f0; background:#fff; display:flex; align-items:center; justify-content:center; transition:all 0.15s; }
.pkg-card.selected .pkg-check { background:#2563eb; border-color:#2563eb; }
.pkg-card.selected .pkg-check svg { display:block; }
.pkg-check svg { display:none; }
</style>
@endpush

@section('content')
<div class="space-y-6 max-w-3xl w-full">

    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.bookings.show', $booking) }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Order of Payment</h1>
            <p class="text-sm text-gray-500">Booking: <strong>{{ $booking->reference_number }}</strong> — {{ $booking->getTypeLabel() }}</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('parishioner.orders.booking.store', $booking) }}" id="order-form">
        @csrf

        {{-- Package selection --}}
        @if($packages->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                <h2 class="font-bold text-gray-900">Choose a Package</h2>
                <p class="text-sm text-gray-500 mt-0.5">Select the package that fits your needs, or choose Standard.</p>
            </div>
            <div class="p-6 space-y-3">
                {{-- Standard / No package option --}}
                <label class="pkg-card" id="pkg-none" onclick="selectPkg(this, null, {{ (float)($service?->fee ?? $booking->service_fee ?? 0) }})">
                    <input type="radio" name="service_package_id" value="" checked>
                    <div class="pkg-check"><svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                    <p class="font-bold text-gray-900">Standard (No Package)</p>
                    <p class="text-sm text-gray-500 mt-0.5">Base service fee only — no add-ons.</p>
                    <p class="text-lg font-bold text-blue-700 mt-2">₱{{ number_format($service?->fee ?? $booking->service_fee ?? 0, 2) }}</p>
                </label>

                @foreach($packages as $pkg)
                <label class="pkg-card" id="pkg-{{ $pkg->id }}" onclick="selectPkg(this, {{ $pkg->id }}, {{ (float)$pkg->price }})">
                    <input type="radio" name="service_package_id" value="{{ $pkg->id }}">
                    <div class="pkg-check"><svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div>
                    <p class="font-bold text-gray-900">{{ $pkg->name }}</p>
                    @if($pkg->description)<p class="text-sm text-gray-500 mt-0.5">{{ $pkg->description }}</p>@endif
                    @if($pkg->inclusionsList())
                    <ul class="mt-2 space-y-0.5">
                        @foreach($pkg->inclusionsList() as $inc)
                        <li class="text-xs text-gray-600 flex items-start gap-1.5">
                            <svg class="w-3 h-3 text-green-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ $inc }}
                        </li>
                        @endforeach
                    </ul>
                    @endif
                    <p class="text-lg font-bold text-blue-700 mt-3">₱{{ number_format($pkg->price, 2) }}</p>
                </label>
                @endforeach
            </div>
        </div>
        @else
        {{-- No packages defined — standard fee only --}}
        <input type="hidden" name="service_package_id" value="">
        <div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 text-sm text-blue-800">
            <p class="font-bold">Standard Service Fee</p>
            <p class="text-2xl font-bold text-blue-900 mt-1">₱{{ number_format($service?->fee ?? $booking->service_fee ?? 0, 2) }}</p>
        </div>
        @endif

        {{-- Total preview --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
            <div class="flex items-center justify-between">
                <span class="text-sm font-semibold text-gray-600">Total Amount Due</span>
                <span class="text-2xl font-bold text-blue-700" id="order-total">
                    ₱{{ number_format($service?->fee ?? $booking->service_fee ?? 0, 2) }}
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-1">Amount is fixed and comes from the selected package/standard fee.</p>
        </div>

        <div>
            <label class="form-label">Notes (optional)</label>
            <textarea name="notes" rows="2" class="form-input w-full text-sm"
                      placeholder="Any additional information for the parish office…">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">Generate Order of Payment</button>
            <a href="{{ route('parishioner.bookings.show', $booking) }}" class="btn-secondary">Back</a>
        </div>
    </form>

</div>

@push('scripts')
<script>
function selectPkg(el, pkgId, price) {
    document.querySelectorAll('.pkg-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    const radio = el.querySelector('input[type=radio]');
    radio.checked = true;
    document.getElementById('order-total').textContent = '₱' + price.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
}
// Pre-select Standard
document.addEventListener('DOMContentLoaded', () => {
    const none = document.getElementById('pkg-none');
    if (none) none.classList.add('selected');
});
</script>
@endpush
@endsection
