@extends('layouts.app')
@section('title', 'Credit & Debit Ledger')
@section('page-title', 'Credit & Debit Ledger')

@section('content')
<div class="py-6 space-y-5">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Overall Balance Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-green-50 border border-green-200 rounded-xl p-5">
            <p class="text-xs font-bold text-green-600 uppercase tracking-wide mb-1">Total Income (All Time)</p>
            <p class="text-3xl font-bold text-green-700">₱{{ number_format($overallCredit, 2) }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-xl p-5">
            <p class="text-xs font-bold text-red-600 uppercase tracking-wide mb-1">Total Expenses (All Time)</p>
            <p class="text-3xl font-bold text-red-700">₱{{ number_format($overallDebit, 2) }}</p>
        </div>
        <div class="{{ ($overallCredit - $overallDebit) >= 0 ? 'bg-blue-50 border-blue-200' : 'bg-red-50 border-red-200' }} border rounded-xl p-5">
            <p class="text-xs font-bold {{ ($overallCredit - $overallDebit) >= 0 ? 'text-blue-600' : 'text-red-600' }} uppercase tracking-wide mb-1">Net Balance</p>
            <p class="text-3xl font-bold {{ ($overallCredit - $overallDebit) >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                ₱{{ number_format(abs($overallCredit - $overallDebit), 2) }}
                @if(($overallCredit - $overallDebit) < 0)<span class="text-sm font-normal">(Deficit)</span>@endif
            </p>
        </div>
    </div>

    {{-- Filters & Actions --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" data-live-search data-target="#ledger-table" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="form-label text-xs">Type</label>
                <select name="type" class="form-select text-sm" data-live-input>
                    <option value="">All</option>
                    <option value="credit" @selected(request('type')==='credit')>Income (Credit)</option>
                    <option value="debit"  @selected(request('type')==='debit')>Expense (Debit)</option>
                </select>
            </div>
            <div>
                <label class="form-label text-xs">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input text-sm" data-live-input>
            </div>
            <div>
                <label class="form-label text-xs">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input text-sm" data-live-input>
            </div>
            <button type="submit" class="btn-secondary text-sm">Filter</button>
            @if(request()->hasAny(['type','from','to','category']))
            <a href="{{ route('admin.ledger.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.ledger.report') }}" class="btn-secondary text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Financial Report
                </a>
                <a href="{{ route('admin.ledger.create') }}" class="btn-primary text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Entry
                </a>
            </div>
        </form>
    </div>

    {{-- Filtered summary --}}
    @if(request()->hasAny(['type','from','to']))
    <div class="flex gap-4 flex-wrap">
        <div class="bg-green-50 border border-green-100 rounded-lg px-4 py-2 text-sm">
            <span class="text-green-600 font-medium">Filtered Income:</span>
            <span class="font-bold text-green-700 ml-1">₱{{ number_format($totalCredit, 2) }}</span>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-lg px-4 py-2 text-sm">
            <span class="text-red-600 font-medium">Filtered Expenses:</span>
            <span class="font-bold text-red-700 ml-1">₱{{ number_format($totalDebit, 2) }}</span>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-2 text-sm">
            <span class="text-blue-600 font-medium">Net:</span>
            <span class="font-bold {{ ($totalCredit-$totalDebit) >= 0 ? 'text-blue-700' : 'text-red-700' }} ml-1">
                ₱{{ number_format(abs($totalCredit-$totalDebit), 2) }}
                {{ ($totalCredit-$totalDebit) < 0 ? '(Deficit)' : '' }}
            </span>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div id="ledger-table" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">Category</th>
                    <th class="px-4 py-3 font-medium">Description</th>
                    <th class="px-4 py-3 font-medium">Ref #</th>
                    <th class="px-4 py-3 font-medium text-right">Amount</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($entries as $entry)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $entry->entry_date->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        @if($entry->type === 'credit')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            Income
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            Expense
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-700 text-xs">{{ $entry->category }}</td>
                    <td class="px-4 py-3 text-gray-800 font-medium">{{ $entry->description }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $entry->reference_number ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-bold {{ $entry->type === 'credit' ? 'text-green-700' : 'text-red-700' }}">
                        {{ $entry->type === 'credit' ? '+' : '-' }}₱{{ number_format($entry->amount, 2) }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('admin.ledger.edit', $entry) }}" class="action-btn action-btn-edit">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.ledger.destroy', $entry) }}"
                                  onsubmit="return confirm('Delete this entry?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No entries found. <a href="{{ route('admin.ledger.create') }}" class="text-blue-600 hover:underline">Add the first entry →</a></td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $entries->links() }}</div>
    </div>
</div>
@endsection
