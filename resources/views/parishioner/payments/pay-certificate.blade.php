@extends('layouts.portal')
@section('title', 'Certificate Fee Payment')

@push('styles')
<style>
.method-tab { cursor:pointer; transition:all 0.2s; }
#tab-gcash.active  { border-color:#007DFE !important; background:#EFF6FF; }
#tab-maya.active   { border-color:#00B140 !important; background:#F0FDF4; }
#tab-cash.active   { border-color:#F59E0B !important; background:#FFFBEB; }
.method-panel { display:none; }
.method-panel.active { display:block; }
.qr-box { border-radius:1rem; padding:1.25rem; text-align:center; }
.qr-gcash { background:linear-gradient(135deg,#EFF6FF,#DBEAFE); border:2px solid #BFDBFE; }
.qr-maya  { background:linear-gradient(135deg,#F0FDF4,#DCFCE7); border:2px solid #BBF7D0; }
.step-num { width:26px;height:26px;border-radius:50%;color:#fff;font-weight:800;font-size:0.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.step-gcash { background:#007DFE; }
.step-maya  { background:#00B140; }
</style>
@endpush

@section('content')
<div class="max-w-lg mx-auto space-y-5 py-4">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.certificates.index') }}"
           class="w-9 h-9 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition shadow-sm">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Certificate Fee Payment</h1>
            <p class="text-sm text-gray-500">Pay the ₱100.00 certificate processing fee</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-800 font-medium">
        {{ session('success') }}
    </div>
    @endif

    {{-- Previous payment rejected notice --}}
    @if(isset($existingPayment) && $existingPayment?->status === 'failed')
    <div class="bg-red-50 border border-red-300 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-9 h-9 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div>
            <p class="font-bold text-red-900 text-sm">Previous Payment Rejected</p>
            @if($existingPayment->rejection_reason)
            <p class="text-sm text-red-700 mt-0.5">Reason: <strong>{{ $existingPayment->rejection_reason }}</strong></p>
            @endif
            <p class="text-xs text-red-600 mt-1">Please submit a new payment below.</p>
        </div>
    </div>
    @endif

    {{-- Certificate Summary --}}
    <div class="bg-gradient-to-br from-purple-700 to-indigo-800 rounded-2xl p-5 text-white shadow-lg">
        <p class="text-purple-200 text-xs font-bold uppercase tracking-wider mb-1">Certificate Request</p>
        <h2 class="text-lg font-bold mb-3">{{ $certificate->getTypeLabel() }}</h2>
        <div class="grid grid-cols-2 gap-3 text-sm mb-4">
            <div>
                <p class="text-purple-300 text-xs">Certificate #</p>
                <p class="font-mono font-semibold text-xs">{{ $certificate->certificate_number }}</p>
            </div>
            <div>
                <p class="text-purple-300 text-xs">Purpose</p>
                <p class="font-semibold text-xs">{{ $certificate->purpose ?? 'Official use' }}</p>
            </div>
        </div>
        <div class="border-t border-purple-500 pt-3">
            <p class="text-purple-200 text-xs">Certificate Fee</p>
            <p class="text-3xl font-extrabold">₱{{ number_format($fee, 2) }}</p>
            <p class="text-xs text-purple-300 mt-1">One-time processing fee</p>
        </div>
    </div>

    {{-- Method Tabs --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-gray-800">Select Payment Method</h3>
        </div>

        {{-- Tab buttons --}}
        <div class="grid grid-cols-3 gap-2 p-4">
            <div class="method-tab active border-2 rounded-xl p-3 text-center" onclick="switchTab('gcash')" id="tab-gcash">
                <div class="w-14 h-9 mx-auto mb-2 flex items-center justify-center">
                    <img src="{{ asset('images/payment/gcash.svg') }}" alt="GCash" class="w-full h-full object-contain rounded-lg">
                </div>
                <p class="text-xs font-extrabold text-gray-900">GCash</p>
                <p class="text-xs font-bold" style="color:#007DFE;">Online</p>
            </div>
            <div class="method-tab border-2 border-gray-200 rounded-xl p-3 text-center" onclick="switchTab('maya')" id="tab-maya">
                <div class="w-14 h-9 mx-auto mb-2 flex items-center justify-center">
                    <img src="{{ asset('images/payment/maya.svg') }}" alt="Maya" class="w-full h-full object-contain rounded-lg">
                </div>
                <p class="text-xs font-extrabold text-gray-900">Maya</p>
                <p class="text-xs font-bold" style="color:#3DDB84;">Online</p>
            </div>
            <div class="method-tab border-2 border-gray-200 rounded-xl p-3 text-center" onclick="switchTab('cash')" id="tab-cash">
                <div class="w-10 h-9 rounded-xl bg-amber-100 mx-auto mb-2 flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="text-xs font-extrabold text-gray-900">Cash</p>
                <p class="text-xs font-bold text-amber-600">In-Person</p>
            </div>
        </div>

        {{-- GCash Panel --}}
        <div id="panel-gcash" class="method-panel active px-5 pb-5">
            <div class="text-center mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Amount to Send</p>
                <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full text-xl font-extrabold bg-blue-600 text-white">
                    ₱{{ number_format($fee, 2) }}
                </span>
                <p class="text-xs text-gray-500 mt-2">For: <strong>{{ $certificate->getTypeLabel() }} Certificate Fee</strong></p>
            </div>
            <div class="qr-box qr-gcash mb-4">
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color:#007DFE;">Scan QR Code with GCash App</p>
                <div class="w-48 h-48 mx-auto mb-3 rounded-2xl overflow-hidden border-4 border-white shadow-lg flex items-center justify-center bg-white">
                    @if(file_exists(public_path('images/payment/gcash-qr.png')))
                        <img src="{{ asset('images/payment/gcash-qr.png') }}" alt="GCash QR" class="w-full h-full object-contain p-1">
                    @else
                        <div class="text-center p-4">
                            <p class="text-xs font-bold" style="color:#007DFE;">GCash QR</p>
                            <p class="text-xs text-gray-400 mt-1">Upload to public/images/payment/gcash-qr.png</p>
                        </div>
                    @endif
                </div>
                <div class="rounded-xl p-3 text-center" style="background:rgba(0,125,254,0.08);">
                    <p class="text-xs text-gray-500 mb-0.5">Send to GCash Number</p>
                    <p class="text-2xl font-extrabold tracking-widest" style="color:#007DFE;">{{ config('parish.gcash.number') }}</p>
                    <p class="text-sm font-bold text-gray-700">{{ config('parish.gcash.name') }}</p>
                </div>
                <div class="mt-3 rounded-xl p-3 text-left" style="background:#FFF7ED;border:1px solid #FED7AA;">
                    <p class="text-xs font-bold text-orange-800 mb-1">⚠ Important</p>
                    <p class="text-xs text-orange-700">Enter <strong>₱{{ number_format($fee, 2) }}</strong> as the amount</p>
                    <p class="text-xs text-orange-700">Put <strong class="font-mono">{{ $certificate->certificate_number }}</strong> in the note/message</p>
                </div>
            </div>

            <form action="{{ route('parishioner.payments.certificate-proof', $certificate) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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
            <div class="text-center mb-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Amount to Send</p>
                <span class="inline-flex items-center gap-2 px-5 py-2 rounded-full text-xl font-extrabold bg-green-600 text-white">
                    ₱{{ number_format($fee, 2) }}
                </span>
                <p class="text-xs text-gray-500 mt-2">For: <strong>{{ $certificate->getTypeLabel() }} Certificate Fee</strong></p>
            </div>
            <div class="qr-box qr-maya mb-4">
                <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color:#00B140;">Scan QR Code with Maya App</p>
                <div class="w-48 h-48 mx-auto mb-3 rounded-2xl overflow-hidden border-4 border-white shadow-lg flex items-center justify-center bg-white">
                    @if(file_exists(public_path('images/payment/maya-qr.png')))
                        <img src="{{ asset('images/payment/maya-qr.png') }}" alt="Maya QR" class="w-full h-full object-contain p-1">
                    @else
                        <div class="text-center p-4">
                            <p class="text-xs font-bold" style="color:#00B140;">Maya QR</p>
                            <p class="text-xs text-gray-400 mt-1">Upload to public/images/payment/maya-qr.png</p>
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
                    <p class="text-xs text-orange-700">Enter <strong>₱{{ number_format($fee, 2) }}</strong> as the amount</p>
                    <p class="text-xs text-orange-700">Put <strong class="font-mono">{{ $certificate->certificate_number }}</strong> in the note/message</p>
                </div>
            </div>

            <form action="{{ route('parishioner.payments.certificate-proof', $certificate) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="payment_method" value="maya">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="step-num step-maya">1</div>
                        <p class="text-sm font-bold text-gray-800">Enter Maya reference number</p>
                    </div>
                    <input type="text" name="submitted_reference" required
                           class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-mono focus:outline-none focus:ring-2"
                           placeholder="e.g. MYA-1234567890"
                           value="{{ old('submitted_reference') }}">
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="step-num step-maya">2</div>
                        <p class="text-sm font-bold text-gray-800">Upload screenshot <span class="font-normal text-gray-400">(optional)</span></p>
                    </div>
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:bg-green-50 transition">
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
                        <p class="text-sm text-amber-800">Bring <strong>₱{{ number_format($fee, 2) }}</strong> to the parish office and present your certificate number.</p>
                        <div class="mt-3 bg-white rounded-lg p-3 border border-amber-200">
                            <p class="text-xs text-gray-500 mb-1">Certificate Number</p>
                            <p class="font-mono font-bold text-gray-900 text-base tracking-wider">{{ $certificate->certificate_number }}</p>
                        </div>
                        <div class="mt-3 text-xs text-amber-700 space-y-1">
                            <p>📅 <strong>Office Hours:</strong> Mon–Fri 8AM–5PM, Sat 8AM–12PM</p>
                            <p>📍 <strong>Location:</strong> {{ config('parish.address') }}</p>
                            <p>📞 <strong>Phone:</strong> {{ config('parish.phone') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <form action="{{ route('parishioner.payments.certificate-cash', $certificate) }}" method="POST" id="cash-form">
                @csrf
                <button type="button" onclick="confirmCash()"
                        class="w-full flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 text-white font-bold py-3.5 rounded-xl transition shadow-md text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    I Will Pay Cash at the Office
                </button>
            </form>
        </div>
    </div>

    {{-- Skip for now --}}
    <div class="text-center">
        <a href="{{ route('parishioner.certificates.index') }}"
           class="text-sm text-gray-400 hover:text-gray-600 underline">
            Skip for now — pay later at the parish office
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
function switchTab(method) {
    ['gcash','maya','cash'].forEach(m => {
        const tab = document.getElementById('tab-' + m);
        if (tab) { tab.classList.remove('active'); tab.style.borderColor = ''; tab.style.background = ''; }
        const panel = document.getElementById('panel-' + m);
        if (panel) panel.classList.remove('active');
    });
    const tab = document.getElementById('tab-' + method);
    if (tab) tab.classList.add('active');
    const panel = document.getElementById('panel-' + method);
    if (panel) panel.classList.add('active');
}

function showFileName(input, textId) {
    const el = document.getElementById(textId);
    if (input.files && input.files[0]) { el.textContent = '✓ ' + input.files[0].name; el.style.color = '#16a34a'; }
}

function confirmCash() {
    if (confirm('You selected Cash payment.\n\nPlease bring ₱{{ number_format($fee, 2) }} to the parish office.\n\nCertificate: {{ $certificate->certificate_number }}\n\nProceed?')) {
        document.getElementById('cash-form').submit();
    }
}
</script>
@endpush
