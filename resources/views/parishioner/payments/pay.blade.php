@extends('layouts.portal')
@section('title', 'Make Payment')

@push('styles')
<style>
/* ── Tab styling ── */
.method-tab { cursor:pointer; transition:all 0.2s; }
#tab-gcash.active  { border-color:#007DFE !important; background:#EFF6FF; }
#tab-maya.active   { border-color:#00B140 !important; background:#F0FDF4; }
#tab-cash.active   { border-color:#F59E0B !important; background:#FFFBEB; }
#tab-card.active   { border-color:#6366f1 !important; background:#EEF2FF; }
.method-panel { display:none; }
.method-panel.active { display:block; }

/* ── QR box ── */
.qr-box { border-radius:1rem; padding:1.25rem; text-align:center; }
.qr-gcash { background:linear-gradient(135deg,#EFF6FF,#DBEAFE); border:2px solid #BFDBFE; }
.qr-maya  { background:linear-gradient(135deg,#F0FDF4,#DCFCE7); border:2px solid #BBF7D0; }

/* ── Amount badge ── */
.amount-badge {
    display:inline-flex; align-items:center; gap:6px;
    padding:8px 20px; border-radius:9999px;
    font-size:1.25rem; font-weight:900;
}
.amount-gcash { background:#007DFE; color:#fff; }
.amount-maya  { background:#00B140; color:#fff; }

/* ── Open app button ── */
.open-app-btn {
    display:flex; align-items:center; justify-content:center; gap:8px;
    width:100%; padding:14px; border-radius:12px;
    font-weight:800; font-size:0.9375rem;
    text-decoration:none; transition:all 0.2s;
    box-shadow:0 4px 14px rgba(0,0,0,0.15);
}
.open-gcash { background:#007DFE; color:#fff; }
.open-gcash:hover { background:#0066CC; transform:translateY(-1px); }
.open-maya  { background:#00B140; color:#fff; }
.open-maya:hover  { background:#009933; transform:translateY(-1px); }

/* ── Step number ── */
.step-num { width:26px;height:26px;border-radius:50%;color:#fff;font-weight:800;font-size:0.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.step-gcash { background:#007DFE; }
.step-maya  { background:#00B140; }

/* ── Responsive ── */
@media (max-width: 479px) {
    .qr-box { padding: 0.875rem; }
    .qr-box img, .qr-box > div { width: 180px !important; height: 180px !important; }
    .amount-badge { font-size: 1rem; padding: 6px 14px; }
    .open-app-btn { padding: 12px; font-size: 0.875rem; }
}
</style>
@endpush

@section('content')
<div class="max-w-lg mx-auto space-y-5 py-4">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.bookings.show', $booking) }}"
           class="w-9 h-9 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition shadow-sm">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Make Payment</h1>
            <p class="text-sm text-gray-500">Choose your preferred payment method</p>
        </div>
    </div>

    {{-- Booking Summary --}}
    <div class="bg-gradient-to-br from-blue-700 to-indigo-800 rounded-2xl p-5 text-white shadow-lg">
        <p class="text-blue-200 text-xs font-bold uppercase tracking-wider mb-1">Booking Summary</p>
        <h2 class="text-lg font-bold mb-3">{{ $booking->getTypeLabel() }}</h2>
        <div class="grid grid-cols-2 gap-3 text-sm mb-4">
            <div>
                <p class="text-blue-300 text-xs">Date</p>
                <p class="font-semibold">{{ $booking->scheduled_date->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-blue-300 text-xs">Reference</p>
                <p class="font-mono font-semibold text-xs">{{ $booking->reference_number }}</p>
            </div>
        </div>
        <div class="border-t border-blue-500 pt-3">
            <p class="text-blue-200 text-xs">Amount Due</p>
            <p class="text-3xl font-extrabold">₱{{ number_format($booking->service_fee, 2) }}</p>
        </div>
    </div>

    {{-- Method Tabs --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-gray-800">Select Payment Method</h3>
        </div>

        {{-- Tab buttons --}}
        <div class="grid grid-cols-4 gap-2 p-4">
            {{-- GCash Tab --}}
            <div class="method-tab active border-2 rounded-xl p-3 text-center cursor-pointer" onclick="switchTab('gcash')" id="tab-gcash">
                <div class="w-14 h-9 mx-auto mb-2 flex items-center justify-center">
                    <img src="{{ asset('images/payment/gcash.svg') }}" alt="GCash"
                         class="w-full h-full object-contain rounded-lg">
                </div>
                <p class="text-xs font-extrabold text-gray-900">GCash</p>
                <p class="text-xs font-bold" style="color:#007DFE;">Online</p>
            </div>

            {{-- Maya Tab --}}
            <div class="method-tab border-2 border-gray-200 rounded-xl p-3 text-center cursor-pointer" onclick="switchTab('maya')" id="tab-maya">
                <div class="w-14 h-9 mx-auto mb-2 flex items-center justify-center">
                    <img src="{{ asset('images/payment/maya.svg') }}" alt="Maya"
                         class="w-full h-full object-contain rounded-lg">
                </div>
                <p class="text-xs font-extrabold text-gray-900">Maya</p>
                <p class="text-xs font-bold" style="color:#3DDB84;">Online</p>
            </div>

            {{-- Cash Tab --}}
            <div class="method-tab border-2 border-gray-200 rounded-xl p-3 text-center cursor-pointer" onclick="switchTab('cash')" id="tab-cash">
                <div class="w-10 h-9 rounded-xl bg-amber-100 mx-auto mb-2 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-extrabold text-gray-900">Cash</p>
                <p class="text-xs font-bold text-amber-600">In-Person</p>
            </div>

            {{-- Card Tab --}}
            <div class="method-tab border-2 border-gray-200 rounded-xl p-3 text-center cursor-pointer" onclick="switchTab('card')" id="tab-card">
                <div class="w-14 h-9 mx-auto mb-2 flex items-center justify-center gap-1">
                    <img src="{{ asset('images/payment/visa.svg') }}" alt="Visa"
                         class="h-7 rounded object-contain">
                    <img src="{{ asset('images/payment/mastercard.svg') }}" alt="MC"
                         class="h-7 rounded object-contain">
                </div>
                <p class="text-xs font-extrabold text-gray-900">Card</p>
                <p class="text-xs font-bold text-indigo-600">Credit/Debit</p>
            </div>
        </div>

        {{-- GCash Panel --}}
        <div id="panel-gcash" class="method-panel active px-5 pb-5">

            {{-- Amount to pay --}}
            <div class="text-center mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Amount to Send</p>
                <span class="amount-badge amount-gcash">₱{{ number_format($booking->service_fee, 2) }}</span>
                <p class="text-xs text-gray-500 mt-2">For: <strong>{{ $booking->getTypeLabel() }}</strong></p>
            </div>

            <div class="qr-box qr-gcash mb-4">
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color:#007DFE;">Scan QR Code with GCash App</p>

                {{-- QR Code --}}
                <div class="w-52 h-52 mx-auto mb-3 rounded-2xl overflow-hidden border-4 border-white shadow-lg flex items-center justify-center bg-white">
                    @if(file_exists(public_path('images/payment/gcash-qr.png')))
                        <img src="{{ asset('images/payment/gcash-qr.png') }}" alt="GCash QR" class="w-full h-full object-contain p-1">
                    @else
                        {{-- Placeholder when QR image not uploaded yet --}}
                        <div class="text-center p-4">
                            <div class="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#007DFE;">
                                <svg viewBox="0 0 40 40" class="w-10 h-10"><rect width="40" height="40" rx="8" fill="#007DFE"/><text x="20" y="26" text-anchor="middle" font-family="Arial" font-weight="900" font-size="14" fill="white">G</text><circle cx="20" cy="20" r="11" stroke="white" stroke-width="2.5" fill="none"/></svg>
                            </div>
                            <p class="text-xs font-bold" style="color:#007DFE;">GCash QR</p>
                            <p class="text-xs text-gray-400 mt-1">Upload QR to<br>public/images/payment/<br>gcash-qr.png</p>
                        </div>
                    @endif
                </div>

                {{-- Account info --}}
                <div class="rounded-xl p-3 text-center" style="background:rgba(0,125,254,0.08);">
                    <p class="text-xs text-gray-500 mb-0.5">Send to GCash Number</p>
                    <p class="text-2xl font-extrabold tracking-widest" style="color:#007DFE;">{{ config('parish.gcash.number') }}</p>
                    <p class="text-sm font-bold text-gray-700">{{ config('parish.gcash.name') }}</p>
                </div>

                {{-- Important note --}}
                <div class="mt-3 rounded-xl p-3 text-left" style="background:#FFF7ED;border:1px solid #FED7AA;">
                    <p class="text-xs font-bold text-orange-800 mb-1">⚠ Important</p>
                    <p class="text-xs text-orange-700">Enter <strong>₱{{ number_format($booking->service_fee, 2) }}</strong> as the amount</p>
                    <p class="text-xs text-orange-700">Put <strong class="font-mono">{{ $booking->reference_number }}</strong> in the note/message</p>
                </div>
            </div>

            {{-- Open GCash App button --}}
            <div class="mb-4">
                {{-- Demo checkout button --}}
                <a href="{{ route('parishioner.payments.demo-checkout', [$booking, 'gcash']) }}"
                   class="open-app-btn open-gcash w-full" style="text-decoration:none;">
                    <svg viewBox="0 0 120 40" xmlns="http://www.w3.org/2000/svg" class="h-5 w-auto flex-shrink-0">
                        <circle cx="18" cy="20" r="14" fill="none" stroke="white" stroke-width="3"/>
                        <path d="M18 13 A7 7 0 1 0 25 20 L20 20" stroke="white" stroke-width="3" fill="none" stroke-linecap="round"/>
                        <line x1="20" y1="20" x2="25" y2="20" stroke="rgba(255,255,255,0.7)" stroke-width="3" stroke-linecap="round"/>
                        <path d="M33 14 Q37 20 33 26" stroke="rgba(255,255,255,0.7)" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <path d="M37 11 Q43 20 37 29" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <text x="50" y="26" font-family="Arial,sans-serif" font-weight="900" font-size="18" fill="white">GCash</text>
                    </svg>
                    <span>Pay ₱{{ number_format($booking->service_fee, 2) }} via GCash</span>
                    <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
                <p class="text-xs text-center text-gray-400 mt-2">Secure GCash checkout</p>
            </div>

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium">After paying, submit your reference</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            {{-- Proof submission form --}}
            <form action="{{ route('parishioner.payments.submit-proof', $booking) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="payment_method" value="gcash">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="step-num step-gcash">1</div>
                        <p class="text-sm font-bold text-gray-800">Enter GCash reference number</p>
                    </div>
                    <input type="text" name="submitted_reference" required
                           class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-mono focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                           placeholder="e.g. 1234567890123456"
                           value="{{ old('submitted_reference') }}">
                    @error('submitted_reference')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="step-num step-gcash">2</div>
                        <p class="text-sm font-bold text-gray-800">Upload screenshot <span class="font-normal text-gray-400">(optional)</span></p>
                    </div>
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition">
                        <svg class="w-7 h-7 text-gray-400 mb-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-xs text-gray-500" id="proof-text-gcash">Tap to upload GCash screenshot</p>
                        <input type="file" name="proof" accept="image/*" class="hidden" onchange="showFileName(this,'proof-text-gcash')">
                    </label>
                </div>
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 text-white font-bold py-3.5 rounded-xl transition shadow-md text-sm"
                        style="background:#007DFE;" onmouseover="this.style.background='#0066CC'" onmouseout="this.style.background='#007DFE'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Submit GCash Payment
                </button>
            </form>
        </div>

        {{-- Maya Panel --}}
        <div id="panel-maya" class="method-panel px-5 pb-5">

            {{-- Amount to pay --}}
            <div class="text-center mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Amount to Send</p>
                <span class="amount-badge amount-maya">₱{{ number_format($booking->service_fee, 2) }}</span>
                <p class="text-xs text-gray-500 mt-2">For: <strong>{{ $booking->getTypeLabel() }}</strong></p>
            </div>

            <div class="qr-box qr-maya mb-4">
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color:#00B140;">Scan QR Code with Maya App</p>

                <div class="w-52 h-52 mx-auto mb-3 rounded-2xl overflow-hidden border-4 border-white shadow-lg flex items-center justify-center bg-white">
                    @if(file_exists(public_path('images/payment/maya-qr.png')))
                        <img src="{{ asset('images/payment/maya-qr.png') }}" alt="Maya QR" class="w-full h-full object-contain p-1">
                    @else
                        <div class="text-center p-4">
                            <div class="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#00B140;">
                                <svg viewBox="0 0 40 40" class="w-10 h-10"><rect width="40" height="40" rx="8" fill="#00B140"/><text x="20" y="26" text-anchor="middle" font-family="Arial" font-weight="900" font-size="11" fill="white">maya</text></svg>
                            </div>
                            <p class="text-xs font-bold" style="color:#00B140;">Maya QR</p>
                            <p class="text-xs text-gray-400 mt-1">Upload QR to<br>public/images/payment/<br>maya-qr.png</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-xl p-3 text-center" style="background:rgba(0,177,64,0.08);">
                    <p class="text-xs text-gray-500 mb-0.5">Send to Maya Number</p>
                    <p class="text-2xl font-extrabold tracking-widest" style="color:#00B140;">{{ config('parish.maya.number') }}</p>
                    <p class="text-sm font-bold text-gray-700">{{ config('parish.maya.name') }}</p>
                </div>

                <div class="mt-3 rounded-xl p-3 text-left" style="background:#FFF7ED;border:1px solid #FED7AA;">
                    <p class="text-xs font-bold text-orange-800 mb-1">⚠ Important</p>
                    <p class="text-xs text-orange-700">Enter <strong>₱{{ number_format($booking->service_fee, 2) }}</strong> as the amount</p>
                    <p class="text-xs text-orange-700">Put <strong class="font-mono">{{ $booking->reference_number }}</strong> in the note/message</p>
                </div>
            </div>

            {{-- Open Maya App button --}}
            <div class="mb-4">
                <a href="{{ route('parishioner.payments.demo-checkout', [$booking, 'maya']) }}"
                   class="open-app-btn open-maya w-full" style="text-decoration:none;">
                    <svg viewBox="0 0 80 40" xmlns="http://www.w3.org/2000/svg" class="h-5 w-auto flex-shrink-0">
                        <text x="4" y="30" font-family="Arial Rounded MT Bold,Arial,sans-serif" font-weight="900" font-size="26" fill="white" letter-spacing="-1">maya</text>
                    </svg>
                    <span>Pay ₱{{ number_format($booking->service_fee, 2) }} via Maya</span>
                    <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
                <p class="text-xs text-center text-gray-400 mt-2">Secure Maya checkout</p>
            </div>

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium">After paying, submit your reference</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <form action="{{ route('parishioner.payments.submit-proof', $booking) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="payment_method" value="maya">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="step-num step-maya">1</div>
                        <p class="text-sm font-bold text-gray-800">Enter Maya reference number</p>
                    </div>
                    <input type="text" name="submitted_reference" required
                           class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-mono focus:outline-none focus:ring-2"
                           style="focus:border-color:#00B140;"
                           placeholder="e.g. MYA-1234567890"
                           value="{{ old('submitted_reference') }}">
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="step-num step-maya">2</div>
                        <p class="text-sm font-bold text-gray-800">Upload screenshot <span class="font-normal text-gray-400">(optional)</span></p>
                    </div>
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:bg-green-50 transition" style="hover:border-color:#00B140;">
                        <svg class="w-7 h-7 text-gray-400 mb-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-xs text-gray-500" id="proof-text-maya">Tap to upload Maya screenshot</p>
                        <input type="file" name="proof" accept="image/*" class="hidden" onchange="showFileName(this,'proof-text-maya')">
                    </label>
                </div>
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 text-white font-bold py-3.5 rounded-xl transition shadow-md text-sm"
                        style="background:#00B140;" onmouseover="this.style.background='#009933'" onmouseout="this.style.background='#00B140'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Submit Maya Payment
                </button>
            </form>
        </div>

        {{-- Cash Panel --}}
        <div id="panel-cash" class="method-panel px-5 pb-5">
            <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-5 mb-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-amber-900 mb-1">Pay at the Parish Office</p>
                        <p class="text-sm text-amber-800">Bring <strong>₱{{ number_format($booking->service_fee, 2) }}</strong> to the parish office and present your booking reference number.</p>
                        <div class="mt-3 bg-white rounded-lg p-3 border border-amber-200">
                            <p class="text-xs text-gray-500 mb-1">Your Booking Reference</p>
                            <p class="font-mono font-bold text-gray-900 text-lg tracking-wider">{{ $booking->reference_number }}</p>
                        </div>
                        <div class="mt-3 text-xs text-amber-700 space-y-1">
                            <p>📅 <strong>Office Hours:</strong> Mon–Fri 8AM–5PM, Sat 8AM–12PM</p>
                            <p>📍 <strong>Location:</strong> {{ config('parish.address') }}</p>
                            <p>📞 <strong>Phone:</strong> {{ config('parish.phone') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <form action="{{ route('parishioner.payments.pay-cash', $booking) }}" method="POST" id="cash-form">
                @csrf
                <button type="button" onclick="confirmCash()"
                        class="w-full flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold py-3.5 rounded-xl transition shadow-md text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    I Will Pay Cash at the Office
                </button>
            </form>
        </div>

        {{-- Card Panel --}}
        <div id="panel-card" class="method-panel px-5 pb-5">
            {{-- Amount --}}
            <div class="text-center mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Amount to Pay</p>
                <span class="amount-badge" style="background:#6366f1;color:#fff;">₱{{ number_format($booking->service_fee, 2) }}</span>
                <p class="text-xs text-gray-500 mt-2">For: <strong>{{ $booking->getTypeLabel() }}</strong></p>
            </div>

            {{-- PayMongo.js card form --}}
            <div id="card-errors" class="hidden mb-3 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700"></div>
            <div id="card-success" class="hidden mb-3 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700"></div>

            <div class="space-y-4">
                {{-- Card Number --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Card Number</label>
                    <div id="card-number-element"
                         class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm bg-white focus-within:border-indigo-400 focus-within:ring-2 focus-within:ring-indigo-100 transition min-h-[46px]">
                    </div>
                </div>

                {{-- Expiry & CVC --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Expiry Date</label>
                        <div id="card-expiry-element"
                             class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm bg-white focus-within:border-indigo-400 transition min-h-[46px]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">CVC</label>
                        <div id="card-cvc-element"
                             class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm bg-white focus-within:border-indigo-400 transition min-h-[46px]">
                        </div>
                    </div>
                </div>

                {{-- Cardholder Name --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Cardholder Name</label>
                    <input type="text" id="card-name"
                           class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                           placeholder="As printed on card"
                           value="{{ auth()->user()->parishioner?->full_name }}">
                </div>

                {{-- Accepted cards --}}
                <div class="flex items-center gap-2 py-2">
                    <span class="text-xs text-gray-400">Accepted:</span>
                    <img src="{{ asset('images/payment/visa.svg') }}" alt="Visa" class="h-6 rounded object-contain">
                    <img src="{{ asset('images/payment/mastercard.svg') }}" alt="Mastercard" class="h-6 rounded object-contain">
                    <span class="px-2 py-0.5 bg-blue-800 text-white text-xs font-bold rounded">AMEX</span>
                    <span class="px-2 py-0.5 bg-gray-600 text-white text-xs font-bold rounded">JCB</span>
                </div>

                <button id="pay-card-btn"
                        onclick="payWithCard()"
                        class="w-full flex items-center justify-center gap-2 text-white font-bold py-3.5 rounded-xl transition shadow-md text-sm"
                        style="background:#6366f1;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span id="pay-card-btn-text">Pay ₱{{ number_format($booking->service_fee, 2) }} with Card</span>
                </button>
            </div>

            <div class="flex items-center gap-2 mt-3 justify-center text-xs text-gray-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Secured by PayMongo · 3D Secure Enabled
            </div>
        </div>

    </div>{{-- /.method panels container --}}

    {{-- Security note --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 justify-center">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        All payments are verified by the parish office before confirmation
    </div>

</div>
@endsection

@push('scripts')
{{-- PayMongo.js for card payments --}}
<script src="https://js.paymongo.com/v2/paymongo.js"></script>
<script>
// ── Tab switching ──────────────────────────────────────────────────────────
function switchTab(method) {
    ['gcash','maya','cash','card'].forEach(m => {
        const tab = document.getElementById('tab-' + m);
        if (tab) {
            tab.classList.remove('active');
            tab.style.borderColor = '';
            tab.style.background  = '';
        }
        const panel = document.getElementById('panel-' + m);
        if (panel) panel.classList.remove('active');
    });

    const tab = document.getElementById('tab-' + method);
    if (tab) tab.classList.add('active');

    const panel = document.getElementById('panel-' + method);
    if (panel) panel.classList.add('active');

    // Mount PayMongo elements when card tab is opened
    if (method === 'card' && !window._cardMounted) {
        mountCardElements();
    }
}

function showFileName(input, textId) {
    const el = document.getElementById(textId);
    if (input.files && input.files[0]) {
        el.textContent = '✓ ' + input.files[0].name;
        el.style.color = '#16a34a';
    }
}

function confirmCash() {
    if (confirm('You selected Cash payment.\n\nPlease bring ₱{{ number_format($booking->service_fee, 2) }} to the parish office.\n\nBooking Reference: {{ $booking->reference_number }}\n\nProceed?')) {
        document.getElementById('cash-form').submit();
    }
}

// ── PayMongo Card Payment ──────────────────────────────────────────────────
const PAYMONGO_PUBLIC_KEY = '{{ config('services.paymongo.public_key') }}';
let paymongo, cardNumber, cardExpiry, cardCvc;
window._cardMounted = false;

// Mount immediately when page loads so elements are ready
document.addEventListener('DOMContentLoaded', function () {
    if (PAYMONGO_PUBLIC_KEY && !PAYMONGO_PUBLIC_KEY.includes('PASTE_YOUR')) {
        // Slight delay to ensure PayMongo.js is fully loaded
        setTimeout(mountCardElements, 300);
    }
});

function mountCardElements() {
    if (window._cardMounted) return;

    if (!PAYMONGO_PUBLIC_KEY || PAYMONGO_PUBLIC_KEY.includes('PASTE_YOUR')) {
        const el = document.getElementById('card-errors');
        if (el) { el.textContent = 'Card payment not configured. Please use another method.'; el.classList.remove('hidden'); }
        return;
    }

    try {
        paymongo = PayMongo(PAYMONGO_PUBLIC_KEY);
        const elements = paymongo.elements();

        const style = {
            base: {
                color: '#1f2937',
                fontFamily: 'ui-sans-serif, system-ui, sans-serif',
                fontSize: '14px',
                '::placeholder': { color: '#9ca3af' },
            },
            invalid: { color: '#ef4444' },
        };

        cardNumber = elements.create('cardNumber', { style });
        cardExpiry = elements.create('cardExpiry', { style });
        cardCvc    = elements.create('cardCvc',    { style });

        cardNumber.mount('#card-number-element');
        cardExpiry.mount('#card-expiry-element');
        cardCvc.mount('#card-cvc-element');

        cardNumber.on('change', handleCardChange);
        cardExpiry.on('change', handleCardChange);
        cardCvc.on('change', handleCardChange);

        window._cardMounted = true;
    } catch (e) {
        console.error('PayMongo.js mount error:', e);
        const el = document.getElementById('card-errors');
        if (el) { el.textContent = 'Card form failed to load. Please refresh.'; el.classList.remove('hidden'); }
    }
}

function handleCardChange(event) {
    const errorEl = document.getElementById('card-errors');
    if (event.error) {
        errorEl.textContent = event.error.message;
        errorEl.classList.remove('hidden');
    } else {
        errorEl.classList.add('hidden');
    }
}

async function payWithCard() {
    const btn     = document.getElementById('pay-card-btn');
    const btnText = document.getElementById('pay-card-btn-text');
    const errorEl = document.getElementById('card-errors');
    const successEl = document.getElementById('card-success');

    errorEl.classList.add('hidden');
    successEl.classList.add('hidden');
    btn.disabled = true;
    btnText.textContent = 'Processing…';

    try {
        // Step 1 — Create Payment Intent via our backend
        const intentRes = await fetch('{{ route('parishioner.payments.initiate') }}', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept':       'application/json',
            },
            body: JSON.stringify({
                method:     'card',
                booking_id: '{{ $booking->id }}',
                amount:     '{{ $booking->service_fee }}',
            }),
        });

        const intentData = await intentRes.json();

        if (!intentData.success) {
            // use_qr means PayMongo keys not working — show specific card error
            const msg = intentData.use_qr
                ? 'Card payment gateway unavailable. Please contact the parish office or try GCash/Maya.'
                : (intentData.error || 'Failed to initialize payment.');
            throw new Error(msg);
        }

        // Step 2 — Create PaymentMethod using PayMongo.js
        const { paymentMethod, error } = await paymongo.createPaymentMethod({
            type:            'card',
            card:            cardNumber,
            billing_details: {
                name:  document.getElementById('card-name').value || '{{ auth()->user()->parishioner?->full_name }}',
                email: '{{ auth()->user()->email ?? '' }}',
                phone: '{{ auth()->user()->parishioner?->contact_number ?? '' }}',
            },
        });

        if (error) {
            throw new Error(error.message);
        }

        // Step 3 — Attach PaymentMethod to Intent via our backend
        const confirmRes = await fetch('{{ route('parishioner.payments.card-confirm') }}', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept':       'application/json',
            },
            body: JSON.stringify({
                payment_intent_id: intentData.payment_intent_id,
                payment_method_id: paymentMethod.id,
                reference_number:  intentData.reference_number,
            }),
        });

        const confirmData = await confirmRes.json();

        if (!confirmData.success) {
            throw new Error(confirmData.error || 'Payment confirmation failed.');
        }

        // Step 4 — Handle result
        if (confirmData.status === 'awaiting_next_action' && confirmData.redirect_url) {
            // 3D Secure required — redirect to bank's auth page
            btnText.textContent = 'Redirecting to 3D Secure…';
            window.location.href = confirmData.redirect_url;
            return;
        }

        if (confirmData.status === 'succeeded') {
            successEl.textContent = '✓ Payment successful! Redirecting…';
            successEl.classList.remove('hidden');
            setTimeout(() => { window.location.href = confirmData.receipt_url; }, 1500);
            return;
        }

        throw new Error('Unexpected payment status: ' + confirmData.status);

    } catch (err) {
        errorEl.textContent = err.message || 'Payment failed. Please try again.';
        errorEl.classList.remove('hidden');
        btn.disabled = false;
        btnText.textContent = 'Pay ₱{{ number_format($booking->service_fee, 2) }} with Card';
    }
}
</script>
@endpush
