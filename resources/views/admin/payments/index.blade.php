@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('content')
<div class="py-6 space-y-4">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @php $total = $summary->sum('total'); @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-2xl font-bold text-green-600 mt-1">₱{{ number_format($total, 2) }}</p>
        </div>
        @foreach($summary as $s)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500 capitalize">{{ $s->payment_method }}</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">₱{{ number_format($s->total, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $s->count }} transactions</p>
        </div>
        @endforeach
    </div>

    {{-- Pending verification alert --}}
    @php $pendingVerification = \App\Models\Payment::where('status','pending')->whereIn('payment_method',['gcash','maya'])->count(); @endphp
    @if($pendingVerification > 0)
    <div class="bg-amber-50 border border-amber-300 rounded-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="font-bold text-amber-900">{{ $pendingVerification }} GCash/Maya payment(s) awaiting verification</p>
                <p class="text-sm text-amber-700">Review submitted reference numbers and proof of payment.</p>
            </div>
        </div>
        <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}"
           class="flex-shrink-0 bg-amber-500 text-white font-bold text-sm px-4 py-2 rounded-lg hover:bg-amber-600 transition">
            Review Now
        </a>
    </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" data-live-search data-target="#payments-table" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-input text-sm w-48" placeholder="Ref # or name…" data-live-input>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="form-select text-sm" data-live-input>
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                    <option value="refunded" @selected(request('status') === 'refunded')>Refunded</option>
                    <option value="voided" @selected(request('status') === 'voided')>Voided</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Method</label>
                <select name="method" class="form-select text-sm" data-live-input>
                    <option value="">All</option>
                    <option value="cash" @selected(request('method') === 'cash')>Cash</option>
                    <option value="gcash" @selected(request('method') === 'gcash')>GCash</option>
                    <option value="paymaya" @selected(request('method') === 'paymaya')>Maya</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm" data-live-input>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm" data-live-input>
            </div>
            <button type="submit" class="btn-secondary text-sm">Filter</button>
            @if(request()->hasAny(['search','status','method','date_from','date_to']))
                <a href="{{ route('admin.payments.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.payments.report') }}" class="btn-secondary text-sm">Reports</a>
                <button type="button" onclick="document.getElementById('cash-modal').classList.remove('hidden')"
                        class="btn-primary text-sm">+ Record Cash</button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div id="payments-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Reference #</th>
                    <th class="px-4 py-3 font-medium">Parishioner</th>
                    <th class="px-4 py-3 font-medium">Amount</th>
                    <th class="px-4 py-3 font-medium">Method</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($payments as $payment)
                @php
                    $statusColors = ['pending' => 'amber', 'paid' => 'green', 'refunded' => 'blue', 'voided' => 'red'];
                    $sc = $statusColors[$payment->status] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $payment->reference_number ?? '—' }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $payment->parishioner?->full_name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-900">₱{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-600 capitalize">{{ $payment->payment_method }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-800">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : $payment->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.payments.show', $payment) }}" class="action-btn action-btn-view">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">No payments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $payments->links() }}
        </div>
    </div>
</div>

{{-- Cash Payment Modal --}}
<div id="cash-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Record Cash Payment</h3>
        <form method="POST" action="{{ route('admin.payments.record-cash') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Parishioner ID <span class="text-red-500">*</span></label>
                <input type="number" name="parishioner_id" required class="form-input w-full" placeholder="Parishioner ID">
            </div>
            <div>
                <label class="form-label">Amount (₱) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" step="0.01" min="1" required class="form-input w-full" placeholder="0.00">
            </div>
            <div>
                <label class="form-label">Booking ID (optional)</label>
                <input type="number" name="booking_id" class="form-input w-full" placeholder="Leave blank if not linked">
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input w-full" placeholder="Payment description…"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1">Record Payment</button>
                <button type="button" onclick="document.getElementById('cash-modal').classList.add('hidden')"
                        class="btn-secondary flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection
