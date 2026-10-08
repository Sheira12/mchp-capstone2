@extends('layouts.portal')
@section('title', 'My Orders')

@section('content')
<div class="space-y-5 max-w-4xl w-full">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Orders of Payment</h1>
        <p class="text-sm text-gray-500 mt-0.5">Track all your parish service orders.</p>
    </div>

    @if($orders->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="font-medium">No orders yet.</p>
        <a href="{{ route('parishioner.bookings.create') }}" class="mt-2 inline-block text-blue-600 hover:underline text-sm">Book a service</a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($orders as $order)
        @php $sc = ['pending'=>'yellow','paid'=>'green','cancelled'=>'red','expired'=>'gray'][$order->status] ?? 'gray'; @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-bold text-gray-900">{{ $order->order_number }}</p>
                <p class="text-sm text-gray-500">{{ $order->booking?->getTypeLabel() ?? $order->service?->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('M d, Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-lg font-bold text-blue-700">₱{{ number_format($order->total, 2) }}</span>
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $sc }}-100 text-{{ $sc }}-700 border border-{{ $sc }}-200">
                    {{ $order->getStatusLabel() }}
                </span>
                <a href="{{ route('parishioner.orders.show', $order) }}" class="text-blue-600 hover:underline text-sm font-semibold">View</a>
            </div>
        </div>
        @endforeach
    </div>
    <div>{{ $orders->links() }}</div>
    @endif
</div>
@endsection
