@extends('layouts.app')
@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

@section('content')
@php
$statusConfig = [
    'pending'   => ['bg'=>'bg-amber-100',  'text'=>'text-amber-800',  'dot'=>'bg-amber-500',  'label'=>'Pending Approval'],
    'confirmed' => ['bg'=>'bg-green-100',  'text'=>'text-green-800',  'dot'=>'bg-green-500',  'label'=>'Confirmed'],
    'completed' => ['bg'=>'bg-blue-100',   'text'=>'text-blue-800',   'dot'=>'bg-blue-500',   'label'=>'Completed'],
    'cancelled' => ['bg'=>'bg-red-100',    'text'=>'text-red-800',    'dot'=>'bg-red-500',    'label'=>'Cancelled'],
];
$sc = $statusConfig[$booking->status] ?? $statusConfig['pending'];
@endphp

<div class="py-6 max-w-4xl space-y-5">

{{-- ── Header ── --}}
<div class="flex items-center justify-between flex-wrap gap-3">
    <a href="{{ route('admin.bookings.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Back to Bookings
    </a>
    <div class="flex items-center gap-3">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-bold {{ $sc['bg'] }} {{ $sc['text'] }}">
            <span class="w-2 h-2 rounded-full {{ $sc['dot'] }}"></span>
            {{ $sc['label'] }}
        </span>
        <span class="text-xs font-mono text-gray-400">{{ $booking->reference_number }}</span>
    </div>
</div>

{{-- ── Pending alert banner ── --}}
@if($booking->status === 'pending')
<div class="bg-amber-50 border border-amber-300 rounded-xl p-4 flex items-start gap-3">
    <div class="w-9 h-9 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <div>
        <p class="font-bold text-amber-900">Action Required — Booking Awaiting Approval</p>
        <p class="text-sm text-amber-700 mt-0.5">
            <strong>{{ $booking->parishioner?->full_name ?? 'Walk-in' }}</strong> has requested
            <strong>{{ $booking->getTypeLabel() }}</strong> on
            <strong>{{ $booking->scheduled_date->format('F d, Y') }}</strong>.
            Please confirm or decline below.
        </p>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

{{-- ── LEFT: Booking Details ── --}}
<div class="lg:col-span-2 space-y-5">

    {{-- Booking Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h2 class="font-bold text-gray-900">{{ $booking->getTypeLabel() }}</h2>
                <p class="text-xs text-gray-500 font-mono">{{ $booking->reference_number }}</p>
            </div>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Scheduled Date</dt>
                    <dd class="font-bold text-gray-900 text-base">{{ $booking->scheduled_date->format('F d, Y') }}</dd>
                    <dd class="text-xs text-gray-500">{{ $booking->scheduled_date->format('l') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Scheduled Time</dt>
                    <dd class="font-bold text-gray-900">{{ $booking->scheduled_time ? \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') : 'To be confirmed' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Service Fee</dt>
                    <dd class="font-bold text-gray-900">{{ $booking->service_fee > 0 ? '₱'.number_format($booking->service_fee,2) : 'Free / Donation' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Submitted</dt>
                    <dd class="font-medium text-gray-700">{{ $booking->created_at->format('M d, Y g:i A') }}</dd>
                </div>
                @if($booking->address)
                <div class="col-span-2">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Address</dt>
                    <dd class="font-medium text-gray-700">{{ $booking->address }}</dd>
                </div>
                @endif
                @if($booking->notes)
                <div class="col-span-2">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Notes from Parishioner</dt>
                    <dd class="text-gray-700 bg-gray-50 rounded-lg p-3 text-sm">{{ $booking->notes }}</dd>
                </div>
                @endif
                @if($booking->admin_notes)
                <div class="col-span-2">
                    <dt class="text-xs font-semibold text-blue-500 uppercase tracking-wide mb-1">Admin Notes</dt>
                    <dd class="text-blue-800 bg-blue-50 rounded-lg p-3 text-sm">{{ $booking->admin_notes }}</dd>
                </div>
                @endif
                @if($booking->cancellation_reason)
                <div class="col-span-2">
                    <dt class="text-xs font-semibold text-red-500 uppercase tracking-wide mb-1">Cancellation Reason</dt>
                    <dd class="text-red-800 bg-red-50 rounded-lg p-3 text-sm">{{ $booking->cancellation_reason }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Payment --}}
    @if($booking->payment)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Payment
        </h2>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Amount</dt>
                <dd class="font-bold text-green-700 text-lg">₱{{ number_format($booking->payment->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Method</dt>
                <dd class="font-medium capitalize">{{ \App\Models\Payment::METHODS[$booking->payment->payment_method] ?? $booking->payment->payment_method }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Status</dt>
                <dd>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $booking->payment->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ ucfirst($booking->payment->status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Receipt No.</dt>
                <dd class="font-mono text-xs text-gray-600">{{ $booking->payment->receipt_number ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    @endif

</div>{{-- /left col --}}

{{-- ── RIGHT: Sidebar ── --}}
<div class="space-y-5">

    {{-- Parishioner Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-bold text-gray-800 text-sm mb-3">Parishioner</h3>
        @if($booking->parishioner)
        <div class="flex items-center gap-3 mb-4">
            @if($booking->parishioner->photo_path)
            <img src="{{ str_starts_with($booking->parishioner->photo_path, 'data:') ? $booking->parishioner->photo_path : Storage::url($booking->parishioner->photo_path) }}" class="w-12 h-12 rounded-full object-cover border-2 border-gray-100" onerror="this.style.display='none'">
            @else
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg">
                {{ substr($booking->parishioner->first_name, 0, 1) }}
            </div>
            @endif
            <div>
                <p class="font-bold text-gray-900">{{ $booking->parishioner->full_name }}</p>
                <p class="text-xs text-gray-500">{{ $booking->parishioner->contact_number ?? 'No contact' }}</p>
                <p class="text-xs text-gray-400">{{ $booking->parishioner->email ?? '' }}</p>
            </div>
        </div>
        <a href="{{ route('admin.parishioners.show', $booking->parishioner) }}"
           class="w-full flex items-center justify-center gap-1.5 text-xs font-semibold text-blue-600 border border-blue-200 bg-blue-50 hover:bg-blue-100 py-2 rounded-lg transition">
            View Full Profile →
        </a>
        @else
        <div class="flex items-center gap-3 py-2">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="font-semibold text-gray-700">Walk-in Booking</p>
                <p class="text-xs text-gray-400">No parishioner account linked</p>
            </div>
        </div>
        @endif
    </div>

    {{-- ── ACTION PANEL ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 bg-gradient-to-r from-slate-50 to-gray-50">
            <h3 class="font-bold text-gray-800 text-sm">Booking Actions</h3>
        </div>
        <div class="p-5 space-y-4">

        {{-- ── PENDING: Confirm + Decline ── --}}
        @if($booking->status === 'pending')

        {{-- CONFIRM --}}
        <div class="rounded-xl border-2 border-green-200 bg-gradient-to-br from-green-50 to-emerald-50 p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="font-bold text-green-800 text-sm">Confirm Booking</p>
                    <p class="text-xs text-green-600">Parishioner will be notified by email</p>
                </div>
            </div>
            <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST" id="confirm-form">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-green-700 mb-1">
                        Message to Parishioner
                        <span class="font-normal text-green-500">(optional)</span>
                    </label>
                    <textarea name="admin_notes" rows="2"
                              class="w-full text-sm border border-green-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400 bg-white resize-none placeholder-green-300"
                              placeholder="e.g. Please bring your documents. See you on {{ $booking->scheduled_date->format('M d') }}!"></textarea>
                </div>
                <button type="button"
                        onclick="showConfirmModal('confirm-form','Confirm Booking','Are you sure you want to confirm this booking for {{ addslashes($booking->parishioner?->full_name ?? 'this walk-in') }}?','Confirm','green')"
                        class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-bold text-sm py-2.5 px-4 rounded-lg transition-all shadow hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Confirm Booking
                </button>
            </form>
        </div>

        {{-- DECLINE --}}
        <div class="rounded-xl border-2 border-red-200 bg-gradient-to-br from-red-50 to-rose-50 p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-red-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="font-bold text-red-800 text-sm">Decline Booking</p>
                    <p class="text-xs text-red-500">Parishioner will be notified with your reason</p>
                </div>
            </div>
            <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" id="decline-form">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-red-700 mb-1">
                        Reason for Declining
                        <span class="text-red-500">*</span>
                    </label>
                    <textarea name="cancellation_reason" rows="2" required
                              class="w-full text-sm border border-red-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 bg-white resize-none placeholder-red-300"
                              placeholder="e.g. The requested date is not available. Please choose another date."></textarea>
                </div>
                <button type="button"
                        onclick="showConfirmModal('decline-form','Decline Booking','Are you sure you want to decline this booking? The parishioner will be notified.','Decline','red')"
                        class="w-full flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold text-sm py-2.5 px-4 rounded-lg transition-all shadow hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Decline Booking
                </button>
            </form>
        </div>
        @endif

        {{-- ── CONFIRMED: Complete + Cancel ── --}}
        @if($booking->status === 'confirmed')
        <div class="rounded-xl border-2 border-blue-200 bg-gradient-to-br from-blue-50 to-sky-50 p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="font-bold text-blue-800 text-sm">Mark as Completed</p>
                    <p class="text-xs text-blue-500">Service has been rendered</p>
                </div>
            </div>
            <form action="{{ route('admin.bookings.complete', $booking) }}" method="POST" id="complete-form">
                @csrf
                <button type="button"
                        onclick="showConfirmModal('complete-form','Mark as Completed','Confirm that the service for this booking has been rendered?','Complete','blue')"
                        class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-2.5 px-4 rounded-lg transition-all shadow hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Mark as Completed
                </button>
            </form>
        </div>
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <p class="text-xs font-bold text-gray-600 mb-2">Cancel This Booking</p>
            <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" id="cancel-form">
                @csrf
                <textarea name="cancellation_reason" rows="2" required
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-400 bg-white resize-none mb-2"
                          placeholder="Reason for cancellation..."></textarea>
                <button type="button"
                        onclick="showConfirmModal('cancel-form','Cancel Booking','Are you sure you want to cancel this confirmed booking?','Cancel','red')"
                        class="w-full text-sm text-red-600 border border-red-200 bg-white hover:bg-red-50 font-semibold py-2 px-4 rounded-lg transition">
                    Cancel Booking
                </button>
            </form>
        </div>
        @endif

        {{-- ── FINAL STATES ── --}}
        @if(in_array($booking->status, ['completed','cancelled']))
        <div class="text-center py-4">
            @if($booking->status === 'completed')
            <div class="inline-flex flex-col items-center gap-2">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="font-bold text-blue-700">Booking Completed</span>
                @if($booking->confirmed_at)
                <span class="text-xs text-gray-400">Confirmed {{ $booking->confirmed_at->format('M d, Y') }}</span>
                @endif
            </div>
            @else
            <div class="inline-flex flex-col items-center gap-2">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <span class="font-bold text-red-700">Booking Cancelled</span>
                @if($booking->cancelled_at)
                <span class="text-xs text-gray-400">Cancelled {{ $booking->cancelled_at->format('M d, Y') }}</span>
                @endif
            </div>
            @endif
        </div>
        @endif

        {{-- ── CASH PAYMENT ── --}}
        @if(!$booking->payment && in_array($booking->status, ['pending','confirmed']) && $booking->service_fee > 0)
        <div class="pt-3 border-t border-gray-100">
            <a href="{{ route('admin.payments.record-cash') }}?booking_id={{ $booking->id }}&parishioner_id={{ $booking->parishioner_id }}&amount={{ $booking->service_fee }}"
               class="w-full flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold text-sm py-2.5 px-4 rounded-lg hover:bg-emerald-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Record Cash Payment (₱{{ number_format($booking->service_fee, 2) }})
            </a>
        </div>
        @endif

        </div>
    </div>{{-- /action panel --}}

    {{-- QR Code --}}
    @if($booking->qrCode)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
        <h3 class="font-semibold text-gray-800 mb-3 text-sm">Booking QR Code</h3>
        @php
            $bqrPath   = $booking->qrCode->qr_image_path;
            $bqrExists = $bqrPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($bqrPath);
        @endphp
        @if($bqrExists)
            <img src="{{ Storage::url($bqrPath) }}" alt="QR Code" class="w-28 h-28 mx-auto border border-gray-100 rounded-xl p-1">
        @else
            <div class="w-28 h-28 mx-auto border-2 border-dashed border-gray-200 rounded-xl flex items-center justify-center text-gray-400 text-xs">No QR</div>
        @endif
        <p class="text-xs text-gray-400 mt-2">Present at parish office for verification</p>
        <a href="{{ $booking->qrCode->verification_url }}" target="_blank"
           class="text-xs text-blue-600 hover:underline mt-1 block">Verify Online →</a>
        <a href="{{ route('admin.bookings.stub', $booking) }}" target="_blank"
           class="mt-3 w-full flex items-center justify-center gap-1.5 text-xs font-semibold text-indigo-600 border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 py-2 rounded-lg transition">
            🖨️ Print Walk-in Stub
        </a>
    </div>
    @endif

</div>{{-- /right col --}}
</div>{{-- /grid --}}
</div>{{-- /py-6 --}}

{{-- ── Confirmation Modal ── --}}
<div id="action-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform transition-all">
        <div class="text-center mb-5">
            <div id="modal-icon" class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3"></div>
            <h3 id="modal-title" class="text-lg font-bold text-gray-900"></h3>
            <p id="modal-message" class="text-sm text-gray-500 mt-1"></p>
        </div>
        <div class="flex gap-3">
            <button onclick="closeModal()"
                    class="flex-1 py-2.5 px-4 border border-gray-300 text-gray-700 font-semibold text-sm rounded-xl hover:bg-gray-50 transition">
                Cancel
            </button>
            <button id="modal-confirm-btn" onclick="submitModalForm()"
                    class="flex-1 py-2.5 px-4 text-white font-bold text-sm rounded-xl transition shadow">
                Confirm
            </button>
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
        green: { bg: 'bg-green-100', icon: 'text-green-600', btn: 'bg-green-600 hover:bg-green-700', svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>' },
        red:   { bg: 'bg-red-100',   icon: 'text-red-600',   btn: 'bg-red-600 hover:bg-red-700',     svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>' },
        blue:  { bg: 'bg-blue-100',  icon: 'text-blue-600',  btn: 'bg-blue-600 hover:bg-blue-700',   svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' },
    };
    const c = colorMap[color] || colorMap.blue;

    document.getElementById('modal-icon').className = `w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-3 ${c.bg}`;
    document.getElementById('modal-icon').innerHTML = `<svg class="w-7 h-7 ${c.icon}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">${c.svg}</svg>`;
    document.getElementById('modal-title').textContent   = title;
    document.getElementById('modal-message').textContent = message;
    document.getElementById('modal-confirm-btn').textContent = btnLabel;
    document.getElementById('modal-confirm-btn').className = `flex-1 py-2.5 px-4 text-white font-bold text-sm rounded-xl transition shadow ${c.btn}`;

    document.getElementById('action-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('action-modal').classList.add('hidden');
    pendingFormId = null;
}

function submitModalForm() {
    if (pendingFormId) {
        const form = document.getElementById(pendingFormId);
        // Validate required fields before submitting
        const required = form.querySelectorAll('[required]');
        for (const field of required) {
            if (!field.value.trim()) {
                closeModal();
                field.focus();
                field.style.borderColor = '#ef4444';
                field.placeholder = '⚠ This field is required';
                return;
            }
        }
        form.submit();
    }
}

// Close modal on backdrop click
document.getElementById('action-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
