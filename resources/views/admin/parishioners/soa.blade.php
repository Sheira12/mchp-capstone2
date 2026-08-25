@extends('layouts.app')
@section('title', 'Statement of Account')
@section('page-title', 'Statement of Account')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.parishioners.show', $parishioner) }}" class="text-sm text-gray-500 hover:text-blue-600">← Back to Profile</a>
            <h2 class="text-xl font-bold text-gray-900 mt-1">{{ $parishioner->full_name }}</h2>
            <p class="text-sm text-gray-500">Statement of Account</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.parishioners.soa-pdf', array_merge(['parishioner' => $parishioner->id], request()->query())) }}"
               target="_blank"
               class="action-btn btn-primary flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Generate PDF
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Total Due</p>
            <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalDue, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Total Paid</p>
            <p class="text-2xl font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-green-100 shadow-sm p-5 {{ $outstanding > 0 ? 'bg-red-50 border-red-200' : 'bg-green-50' }}">
            <p class="text-xs {{ $outstanding > 0 ? 'text-red-500' : 'text-green-500' }} uppercase tracking-wide font-semibold mb-1">Outstanding Balance</p>
            <p class="text-2xl font-bold {{ $outstanding > 0 ? 'text-red-600' : 'text-green-600' }}">₱{{ number_format($outstanding, 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="parishioner" value="{{ $parishioner->id }}">
            <div>
                <label class="form-label text-xs">From Date</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-sm">
            </div>
            <div>
                <label class="form-label text-xs">To Date</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-sm">
            </div>
            <div>
                <label class="form-label text-xs">Payment Method</label>
                <select name="type" class="form-select text-sm">
                    <option value="">All Methods</option>
                    @foreach(\App\Models\Payment::METHODS as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="action-btn btn-primary btn-sm">Filter</button>
            @if(request()->hasAny(['from','to','type']))
                <a href="{{ route('admin.parishioners.soa', $parishioner) }}" class="action-btn btn-ghost btn-sm">Clear</a>
            @endif
        </form>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Transaction History</h3>
            <span class="text-sm text-gray-400">{{ $payments->count() }} record(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500">
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Description</th>
                        <th class="px-4 py-3 font-medium">Method</th>
                        <th class="px-4 py-3 font-medium text-center">Type</th>
                        <th class="px-4 py-3 font-medium text-right">Amount Due</th>
                        <th class="px-4 py-3 font-medium text-right">Amount Paid</th>
                        <th class="px-4 py-3 font-medium text-right">Balance</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php $runningBalance = 0; @endphp
                    @forelse($payments as $payment)
                    @php
                        $due     = $payment->booking?->service_fee ?? 0;
                        $paid    = $payment->status === 'paid' ? $payment->amount : 0;
                        $runningBalance += ($due - $paid);
                        $statusColor = match($payment->status) {
                            'paid'     => 'green',
                            'pending'  => 'amber',
                            'failed'   => 'red',
                            'refunded' => 'blue',
                            default    => 'gray',
                        };
                        $badge = $payment->transaction_type_badge;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            {{ $payment->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            @if($payment->booking)
                                <span class="font-medium">{{ $payment->booking->getTypeLabel() }}</span>
                            @elseif($payment->certificate)
                                <span class="font-medium">{{ $payment->certificate->getTypeLabel() }}</span>
                            @else
                                <span class="text-gray-500">{{ $payment->notes ?? 'Parish Service' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ \App\Models\Payment::METHODS[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $badge['color'] === 'green' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $badge['color'] === 'green' ? '▲' : '▼' }} {{ $badge['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">₱{{ number_format($due, 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-green-600">
                            @if($paid > 0) ₱{{ number_format($paid, 2) }} @else — @endif
                        </td>
                        <td class="px-4 py-3 text-right {{ $runningBalance > 0 ? 'text-red-600' : 'text-green-600' }} font-semibold">
                            ₱{{ number_format(max(0, $runningBalance), 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">No transactions found.</td></tr>
                    @endforelse
                </tbody>
                @if($payments->count())
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr>
                        <td colspan="4" class="px-4 py-3 font-bold text-gray-700 text-right">TOTALS:</td>
                        <td class="px-4 py-3 text-right font-bold text-gray-900">₱{{ number_format($totalDue, 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $outstanding > 0 ? 'text-red-600' : 'text-green-600' }}">
                            ₱{{ number_format($outstanding, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
