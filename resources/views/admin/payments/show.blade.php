@extends('layouts.app')
@section('title', 'Payment Details')
@section('page-title', 'Payment Details')

@section('content')
@php
$statusConfig = [
    'pending'  => ['bg'=>'bg-amber-100', 'text'=>'text-amber-800', 'dot'=>'bg-amber-500', 'label'=>'Pending Verification'],
    'paid'     => ['bg'=>'bg-green-100', 'text'=>'text-green-800', 'dot'=>'bg-green-500', 'label'=>'Paid & Verified'],
    'failed'   => ['bg'=>'bg-red-100',   'text'=>'text-red-800',   'dot'=>'bg-red-500',   'label'=>'Rejected'],
    'refunded' => ['bg'=>'bg-blue-100',  'text'=>'text-blue-800',  'dot'=>'bg-blue-500',  'label'=>'Refunded'],
    'voided'   => ['bg'=>'bg-gray-100',  'text'=>'text-gray-800',  'dot'=>'bg-gray-500',  'label'=>'Voided'],
];
$sc = $statusConfig[$payment->status] ?? $statusConfig['pending'];
@endphp

<div class="py-6 max-w-3xl space-y-5">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.payments.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Payments
        </a>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold {{ $sc['bg'] }} {{ $sc['text'] }}">
            <span class="w-2 h-2 rounded-full {{ $sc['dot'] }}"></span>
            {{ $sc['label'] }}
        </span>
    </div>

    {{-- Pending verification alert --}}
    @if($payment->status === 'pending' && in_array($payment->payment_method, ['gcash','maya']))
    <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 flex items-start gap-3">
        <div class="w-9 h-9 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="font-bold text-amber-900">Action Required — Payment Awaiting Verification</p>
            <p class="text-sm text-amber-700 mt-0.5">
                <strong>{{ $payment->parishioner?->full_name }}</strong> submitted a
                <strong>{{ strtoupper($payment->payment_method) }}</strong> payment of
                <strong>₱{{ number_format($payment->amount, 2) }}</strong>.
                Please verify the reference number and proof below.
            </p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- LEFT: Payment Details --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Payment Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h2 class="font-bold text-gray-900">Payment Information</h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-5">
                        <div class="w-14 h-14 rounded-2xl bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-2xl font-extrabold text-gray-900">₱{{ number_format($payment->amount, 2) }}</p>
                            <p class="text-sm text-gray-500 capitalize">{{ \App\Models\Payment::METHODS[$payment->payment_method] ?? ucfirst($payment->payment_method) }}</p>
                            @php $badge = $payment->transaction_type_badge; @endphp
                            <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $badge['color'] === 'green' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $badge['color'] === 'green' ? '▲' : '▼' }} {{ $badge['label'] }}
                            </span>
                        </div>
                    </div>

                    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">System Reference</dt>
                            <dd class="font-mono font-bold text-gray-800">{{ $payment->reference_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Receipt Number</dt>
                            <dd class="font-mono text-gray-700">{{ $payment->receipt_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Parishioner</dt>
                            <dd class="font-bold text-gray-900">
                                @if($payment->parishioner)
                                <a href="{{ route('admin.parishioners.show', $payment->parishioner) }}" class="hover:text-blue-700">
                                    {{ $payment->parishioner->full_name }}
                                </a>
                                @else —
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Contact</dt>
                            <dd class="font-medium text-gray-700">{{ $payment->payer_contact ?? $payment->parishioner?->contact_number ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Submitted At</dt>
                            <dd class="font-medium text-gray-700">{{ $payment->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Paid At</dt>
                            <dd class="font-medium text-gray-700">{{ $payment->paid_at?->format('M d, Y g:i A') ?? '—' }}</dd>
                        </div>
                        @if($payment->submitted_reference)
                        <div class="col-span-2">
                            <dt class="text-xs font-semibold text-amber-500 uppercase tracking-wide mb-1">Submitted Reference Number</dt>
                            <dd class="font-mono font-bold text-gray-900 text-base bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                {{ $payment->submitted_reference }}
                            </dd>
                        </div>
                        @endif
                        @if($payment->booking)
                        <div class="col-span-2">
                            <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Linked Booking</dt>
                            <dd>
                                <a href="{{ route('admin.bookings.show', $payment->booking) }}"
                                   class="inline-flex items-center gap-2 text-blue-600 hover:underline font-medium text-sm">
                                    {{ $payment->booking->getTypeLabel() }} — {{ $payment->booking->scheduled_date->format('M d, Y') }}
                                    <span class="text-xs font-mono text-gray-400">{{ $payment->booking->reference_number }}</span>
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($payment->notes)
                        <div class="col-span-2">
                            <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Notes</dt>
                            <dd class="text-gray-600 bg-gray-50 rounded-lg p-3 text-sm">{{ $payment->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Proof of Payment --}}
            @if($payment->proof_path)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-amber-50 to-yellow-50">
                    <h2 class="font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Proof of Payment
                    </h2>
                </div>
                <div class="p-6">
                    <img src="{{ Storage::url($payment->proof_path) }}" alt="Payment Proof"
                         class="max-w-full rounded-xl border border-gray-200 shadow-sm cursor-pointer"
                         onclick="window.open(this.src,'_blank')"
                         style="max-height:400px;object-fit:contain;">
                    <p class="text-xs text-gray-400 mt-2">Click image to view full size</p>
                </div>
            </div>
            @endif

            {{-- Verification/Rejection history --}}
            @if($payment->verified_at)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                @if($payment->status === 'paid')
                <div class="flex items-center gap-3 bg-green-50 rounded-xl p-4">
                    <div class="w-9 h-9 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-green-800">Payment Verified</p>
                        <p class="text-sm text-green-700">
                            Verified by <strong>{{ $payment->verifiedBy?->name ?? 'Admin' }}</strong>
                            on {{ $payment->verified_at->format('M d, Y g:i A') }}
                        </p>
                    </div>
                </div>
                @elseif($payment->status === 'failed')
                <div class="flex items-start gap-3 bg-red-50 rounded-xl p-4">
                    <div class="w-9 h-9 rounded-full bg-red-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-red-800">Payment Rejected</p>
                        <p class="text-sm text-red-700">{{ $payment->rejection_reason }}</p>
                        <p class="text-xs text-red-500 mt-1">by {{ $payment->verifiedBy?->name }} on {{ $payment->verified_at->format('M d, Y') }}</p>
                    </div>
                </div>
                @endif
            </div>
            @endif

        </div>

        {{-- RIGHT: Actions --}}
        <div class="space-y-5">

            {{-- Verify / Reject for pending GCash/Maya --}}
            @if($payment->status === 'pending' && in_array($payment->payment_method, ['gcash','maya']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50">
                    <h3 class="font-bold text-gray-800 text-sm">Verify Payment</h3>
                </div>
                <div class="p-5 space-y-4">

                    {{-- VERIFY --}}
                    <div class="rounded-xl border-2 border-green-200 bg-green-50 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p class="font-bold text-green-800 text-sm">Approve Payment</p>
                        </div>
                        <p class="text-xs text-green-700 mb-3">
                            Confirm that ₱{{ number_format($payment->amount, 2) }} was received via {{ strtoupper($payment->payment_method) }}.
                            @if($payment->submitted_reference)
                            Reference: <strong class="font-mono">{{ $payment->submitted_reference }}</strong>
                            @endif
                        </p>
                        <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" id="verify-form">
                            @csrf
                            <button type="button"
                                    onclick="showConfirmModal('verify-form','Approve Payment','Confirm that this {{ strtoupper($payment->payment_method) }} payment of ₱{{ number_format($payment->amount,2) }} has been received?','Approve','green')"
                                    class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold text-sm py-2.5 rounded-lg transition shadow">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                Approve & Mark as Paid
                            </button>
                        </form>
                    </div>

                    {{-- REJECT --}}
                    <div class="rounded-xl border-2 border-red-200 bg-red-50 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-7 h-7 rounded-full bg-red-600 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                            <p class="font-bold text-red-800 text-sm">Reject Proof</p>
                        </div>
                        <form action="{{ route('admin.payments.reject', $payment) }}" method="POST" id="reject-form">
                            @csrf
                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-red-700 mb-1">Reason <span class="text-red-500">*</span></label>
                                <textarea name="rejection_reason" rows="2" required
                                          class="w-full text-sm border border-red-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 bg-white resize-none"
                                          placeholder="e.g. Reference number not found. Please resubmit."></textarea>
                            </div>
                            <button type="button"
                                    onclick="showConfirmModal('reject-form','Reject Payment','Reject this payment proof? The parishioner will need to resubmit.','Reject','red')"
                                    class="w-full flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white font-bold text-sm py-2.5 rounded-lg transition shadow">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reject Proof
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- Cash pending verification --}}
            @if($payment->status === 'pending' && $payment->payment_method === 'cash')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-amber-50">
                    <h3 class="font-bold text-amber-800 text-sm">Cash Payment Pending</h3>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-600 mb-4">Parishioner will pay cash at the office. Once received, mark as paid.</p>
                    <form action="{{ route('admin.payments.verify', $payment) }}" method="POST" id="cash-verify-form">
                        @csrf
                        <button type="button"
                                onclick="showConfirmModal('cash-verify-form','Mark Cash as Received','Confirm that ₱{{ number_format($payment->amount,2) }} cash has been received?','Confirm','green')"
                                class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold text-sm py-2.5 rounded-lg transition shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Mark Cash as Received
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Refund/Void for paid --}}
            @if($payment->status === 'paid')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h3 class="font-semibold text-gray-800 text-sm">Payment Actions</h3>
                <form method="POST" action="{{ route('admin.payments.refund', $payment) }}" class="space-y-2">
                    @csrf
                    <input type="text" name="refund_reason" required class="form-input w-full text-sm" placeholder="Refund reason...">
                    <button type="submit" class="w-full btn-secondary text-sm"
                            onclick="return confirm('Refund this payment?')">Issue Refund</button>
                </form>
                <form method="POST" action="{{ route('admin.payments.void', $payment) }}" class="space-y-2">
                    @csrf
                    <input type="text" name="void_reason" required class="form-input w-full text-sm" placeholder="Void reason...">
                    <button type="submit" class="w-full btn-danger text-sm"
                            onclick="return confirm('Void this payment?')">Void Payment</button>
                </form>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div id="action-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <div class="text-center mb-5">
            <div id="modal-icon" class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3"></div>
            <h3 id="modal-title" class="text-lg font-bold text-gray-900"></h3>
            <p id="modal-message" class="text-sm text-gray-500 mt-1"></p>
        </div>
        <div class="flex gap-3">
            <button onclick="closeModal()" class="flex-1 py-2.5 px-4 border border-gray-300 text-gray-700 font-semibold text-sm rounded-xl hover:bg-gray-50 transition">Cancel</button>
            <button id="modal-confirm-btn" onclick="submitModalForm()" class="flex-1 py-2.5 px-4 text-white font-bold text-sm rounded-xl transition shadow">Confirm</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let pendingFormId = null;
function showConfirmModal(formId, title, message, btnLabel, color) {
    pendingFormId = formId;
    const colorMap = {
        green: { bg:'bg-green-100', icon:'text-green-600', btn:'bg-green-600 hover:bg-green-700', svg:'<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>' },
        red:   { bg:'bg-red-100',   icon:'text-red-600',   btn:'bg-red-600 hover:bg-red-700',     svg:'<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>' },
    };
    const c = colorMap[color] || colorMap.green;
    document.getElementById('modal-icon').className = `w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 ${c.bg}`;
    document.getElementById('modal-icon').innerHTML = `<svg class="w-7 h-7 ${c.icon}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">${c.svg}</svg>`;
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-message').textContent = message;
    document.getElementById('modal-confirm-btn').textContent = btnLabel;
    document.getElementById('modal-confirm-btn').className = `flex-1 py-2.5 px-4 text-white font-bold text-sm rounded-xl transition shadow ${c.btn}`;
    document.getElementById('action-modal').classList.remove('hidden');
}
function closeModal() { document.getElementById('action-modal').classList.add('hidden'); pendingFormId = null; }
function submitModalForm() { if (pendingFormId) { const f = document.getElementById(pendingFormId); const req = f.querySelectorAll('[required]'); for (const r of req) { if (!r.value.trim()) { closeModal(); r.focus(); return; } } f.submit(); } }
document.getElementById('action-modal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
</script>
@endpush
