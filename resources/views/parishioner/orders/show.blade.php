@extends('layouts.portal')
@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="space-y-6 max-w-3xl w-full">

    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.bookings.show', $order->booking) }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Order of Payment</h1>
            <p class="text-sm text-gray-500">{{ $order->order_number }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    {{-- Status banner --}}
    @php
        $statusColors = ['pending'=>'yellow','paid'=>'green','cancelled'=>'red','expired'=>'gray'];
        $sc = $statusColors[$order->status] ?? 'gray';
    @endphp
    <div class="flex items-center gap-3 bg-{{ $sc }}-50 border border-{{ $sc }}-200 rounded-xl px-5 py-3">
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-{{ $sc }}-100 text-{{ $sc }}-700 border border-{{ $sc }}-200">
            {{ $order->getStatusLabel() }}
        </span>
        @if($order->status === 'pending' && $order->expires_at)
        <span class="text-xs text-{{ $sc }}-700">Expires {{ $order->expires_at->diffForHumans() }}</span>
        @elseif($order->status === 'paid')
        <span class="text-xs text-green-700 font-semibold">✓ Payment confirmed</span>
        @endif
    </div>

    {{-- OP Document --}}
    <div class="bg-white rounded-2xl border-2 border-blue-200 shadow-sm overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-800 to-blue-600 text-white px-8 py-6 text-center">
            <p class="text-xs font-bold tracking-widest uppercase text-blue-200 mb-1">{{ config('parish.name') }}</p>
            <h2 class="text-xl font-bold">ORDER OF PAYMENT</h2>
            <p class="text-sm text-blue-200 mt-1">{{ $order->order_number }}</p>
        </div>

        <div class="px-8 py-6 space-y-5">
            {{-- Client info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Parishioner</p>
                    <p class="font-bold text-gray-900">{{ $order->parishioner->full_name }}</p>
                    <p class="text-gray-600">{{ $order->parishioner->contact_number }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Service</p>
                    <p class="font-bold text-gray-900">{{ $order->booking?->getTypeLabel() ?? $order->service?->name }}</p>
                    @if($order->booking?->scheduled_date)
                    <p class="text-gray-600">{{ $order->booking->scheduled_date->format('F d, Y') }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Order Date</p>
                    <p class="text-gray-800">{{ $order->created_at->format('F d, Y g:ia') }}</p>
                </div>
                @if($order->expires_at && $order->status === 'pending')
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Valid Until</p>
                    <p class="text-gray-800">{{ $order->expires_at->format('F d, Y g:ia') }}</p>
                </div>
                @endif
            </div>

            {{-- Line items --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold text-gray-600">Description</th>
                            <th class="px-4 py-2.5 text-right font-semibold text-gray-600">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($order->lineItemsList() as $item)
                        <tr>
                            <td class="px-4 py-2.5 text-gray-800">{{ $item['label'] }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-900 font-medium">
                                @if($item['amount'] > 0)
                                ₱{{ number_format($item['amount'], 2) }}
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-blue-50">
                        <tr>
                            <td class="px-4 py-3 font-bold text-gray-900 text-right">TOTAL AMOUNT DUE</td>
                            <td class="px-4 py-3 font-bold text-blue-700 text-right text-lg">₱{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($order->notes)
            <div class="text-sm text-gray-600 italic bg-gray-50 rounded-lg px-4 py-2 border border-gray-100">
                <span class="font-semibold not-italic">Notes:</span> {{ $order->notes }}
            </div>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-wrap gap-3">
        @if($order->status === 'pending')
            @if($order->booking)
            <a href="{{ route('parishioner.payments.pay', $order->booking) }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2.5 rounded-xl transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Proceed to Payment
            </a>
            @endif
            <a href="{{ route('parishioner.orders.pdf', $order) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold px-5 py-2.5 rounded-xl transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF
            </a>
        @elseif($order->status === 'paid')
            @if($order->payment)
            <a href="{{ route('parishioner.payments.receipt', $order->payment) }}"
               class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold px-5 py-2.5 rounded-xl transition text-sm">
                View Official Receipt
            </a>
            @endif
            <a href="{{ route('parishioner.orders.pdf', $order) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold px-5 py-2.5 rounded-xl transition text-sm">
                Download Order PDF
            </a>
        @endif
    </div>

</div>
@endsection
