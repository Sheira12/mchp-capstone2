@extends('layouts.app')

@section('title', 'Booking Details')
@section('page-title', 'Booking Details')

@section('content')
<div class="py-6 max-w-4xl space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.bookings.index') }}" class="text-sm text-blue-600 hover:underline">← Back to Bookings</a>
        @php $statusColors = ['pending' => 'amber', 'confirmed' => 'green', 'completed' => 'blue', 'cancelled' => 'red']; @endphp
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $statusColors[$booking->status] ?? 'gray' }}-100 text-{{ $statusColors[$booking->status] ?? 'gray' }}-800">
            {{ $booking->getStatusLabel() }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Main Details --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-800 mb-4">Booking Information</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Reference Number</dt>
                        <dd class="font-mono font-medium">{{ $booking->reference_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Service Type</dt>
                        <dd class="font-medium">{{ $booking->getTypeLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Scheduled Date</dt>
                        <dd class="font-medium">{{ $booking->scheduled_date->format('F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Scheduled Time</dt>
                        <dd class="font-medium">{{ $booking->scheduled_time ? \Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Service Fee</dt>
                        <dd class="font-medium">₱{{ number_format($booking->service_fee, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Address</dt>
                        <dd class="font-medium">{{ $booking->address ?? '—' }}</dd>
                    </div>
                    @if($booking->notes)
                    <div class="col-span-2">
                        <dt class="text-gray-500">Notes from Parishioner</dt>
                        <dd class="font-medium">{{ $booking->notes }}</dd>
                    </div>
                    @endif
                    @if($booking->admin_notes)
                    <div class="col-span-2">
                        <dt class="text-gray-500">Admin Notes</dt>
                        <dd class="font-medium">{{ $booking->admin_notes }}</dd>
                    </div>
                    @endif
                    @if($booking->cancellation_reason)
                    <div class="col-span-2">
                        <dt class="text-gray-500 text-red-500">Cancellation Reason</dt>
                        <dd class="font-medium text-red-700">{{ $booking->cancellation_reason }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            {{-- Payment --}}
            @if($booking->payment)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-800 mb-4">Payment</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Amount</dt>
                        <dd class="font-medium text-green-700">₱{{ number_format($booking->payment->amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Method</dt>
                        <dd class="font-medium">{{ \App\Models\Payment::METHODS[$booking->payment->payment_method] ?? $booking->payment->payment_method }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $booking->payment->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($booking->payment->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Receipt</dt>
                        <dd class="font-mono text-xs">{{ $booking->payment->receipt_number ?? '—' }}</dd>
                    </div>
                </dl>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            {{-- Parishioner --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Parishioner</h3>
                <div class="flex items-center gap-3 mb-3">
                    @if($booking->parishioner->photo_path)
                    <img src="{{ Storage::url($booking->parishioner->photo_path) }}" class="w-10 h-10 rounded-full object-cover">
                    @else
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold">
                        {{ substr($booking->parishioner->first_name, 0, 1) }}
                    </div>
                    @endif
                    <div>
                        <p class="font-medium text-sm">{{ $booking->parishioner->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $booking->parishioner->contact_number }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.parishioners.show', $booking->parishioner) }}" class="text-xs text-blue-600 hover:underline">View Profile →</a>
            </div>

            {{-- Actions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                <h3 class="font-semibold text-gray-800 mb-3">Actions</h3>

                @if($booking->status === 'pending')
                <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <textarea name="admin_notes" rows="2" class="form-input w-full text-sm" placeholder="Admin notes (optional)..."></textarea>
                    </div>
                    <button type="submit" class="w-full btn-primary text-sm">✓ Confirm Booking</button>
                </form>
                @endif

                @if($booking->status === 'confirmed')
                <form action="{{ route('admin.bookings.complete', $booking) }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 text-white text-sm py-2 px-4 rounded-lg hover:bg-blue-700">Mark as Completed</button>
                </form>
                @endif

                @if(in_array($booking->status, ['pending', 'confirmed']))
                <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Cancel this booking?')">
                    @csrf
                    <div class="mb-2">
                        <textarea name="cancellation_reason" rows="2" class="form-input w-full text-sm" placeholder="Reason for cancellation..." required></textarea>
                    </div>
                    <button type="submit" class="w-full bg-red-50 text-red-700 border border-red-200 text-sm py-2 px-4 rounded-lg hover:bg-red-100">Cancel Booking</button>
                </form>
                @endif

                @if(!$booking->payment && in_array($booking->status, ['pending', 'confirmed']))
                <a href="{{ route('admin.payments.record-cash') }}?booking_id={{ $booking->id }}&parishioner_id={{ $booking->parishioner_id }}&amount={{ $booking->service_fee }}" class="block w-full text-center bg-green-50 text-green-700 border border-green-200 text-sm py-2 px-4 rounded-lg hover:bg-green-100">
                    Record Cash Payment
                </a>
                @endif
            </div>

            {{-- QR Code --}}
            @if($booking->qrCode)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
                <h3 class="font-semibold text-gray-800 mb-3">QR Code</h3>
                @if($booking->qrCode->qr_image_path)
                <img src="{{ Storage::url($booking->qrCode->qr_image_path) }}" alt="QR Code" class="w-32 h-32 mx-auto">
                @endif
                <p class="text-xs text-gray-400 mt-2">Scan to verify booking</p>
                <a href="{{ $booking->qrCode->verification_url }}" target="_blank" class="text-xs text-blue-600 hover:underline">Verify Link</a>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
