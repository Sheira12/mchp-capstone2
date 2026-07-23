@extends('layouts.app')
@section('title', 'Add Ledger Entry')
@section('page-title', 'Add Ledger Entry')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.ledger.store') }}" class="space-y-5" id="ledger-form">
            @csrf

            {{-- Type selector --}}
            <div>
                <label class="form-label">Entry Type <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3 mt-1">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="credit" class="sr-only peer"
                               {{ old('type','credit') === 'credit' ? 'checked' : '' }}
                               onchange="updateCategories('credit')">
                        <div class="border-2 rounded-xl p-4 text-center peer-checked:border-green-500 peer-checked:bg-green-50 border-gray-200 hover:border-green-300 transition">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            </div>
                            <p class="font-bold text-green-700">Income (Credit)</p>
                            <p class="text-xs text-gray-400 mt-0.5">Money received</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="debit" class="sr-only peer"
                               {{ old('type') === 'debit' ? 'checked' : '' }}
                               onchange="updateCategories('debit')">
                        <div class="border-2 rounded-xl p-4 text-center peer-checked:border-red-500 peer-checked:bg-red-50 border-gray-200 hover:border-red-300 transition">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            </div>
                            <p class="font-bold text-red-700">Expense (Debit)</p>
                            <p class="text-xs text-gray-400 mt-0.5">Money spent</p>
                        </div>
                    </label>
                </div>
                @error('type')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Category --}}
            <div>
                <label class="form-label">Category <span class="text-red-500">*</span></label>
                <select name="category" id="category-select" class="form-select w-full @error('category') border-red-400 @enderror" required>
                    <option value="">Select category…</option>
                    @foreach(\App\Models\LedgerEntry::CREDIT_CATEGORIES as $key => $label)
                    <option value="{{ $key }}" data-type="credit" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                    @foreach(\App\Models\LedgerEntry::DEBIT_CATEGORIES as $key => $label)
                    <option value="{{ $key }}" data-type="debit" class="hidden" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Description --}}
            <div>
                <label class="form-label">Description <span class="text-red-500">*</span></label>
                <input type="text" name="description" value="{{ old('description') }}" required
                       class="form-input w-full @error('description') border-red-400 @enderror"
                       placeholder="e.g. Sunday collection — July 20, 2026">
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Amount --}}
                <div>
                    <label class="form-label">Amount (₱) <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium">₱</span>
                        <input type="number" name="amount" value="{{ old('amount') }}" required min="0.01" step="0.01"
                               class="form-input w-full pl-7 @error('amount') border-red-400 @enderror"
                               placeholder="0.00">
                    </div>
                    @error('amount')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Date --}}
                <div>
                    <label class="form-label">Entry Date <span class="text-red-500">*</span></label>
                    <input type="date" name="entry_date" value="{{ old('entry_date', today()->toDateString()) }}" required
                           class="form-input w-full @error('entry_date') border-red-400 @enderror">
                    @error('entry_date')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Reference # --}}
            <div>
                <label class="form-label">Reference Number <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
                <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                       class="form-input w-full" placeholder="OR #, Receipt #, etc.">
            </div>

            {{-- Notes --}}
            <div>
                <label class="form-label">Notes <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
                <textarea name="notes" rows="2" class="form-input w-full"
                          placeholder="Additional details…">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save Entry</button>
                <a href="{{ route('admin.ledger.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function updateCategories(type) {
    const sel = document.getElementById('category-select');
    const opts = sel.querySelectorAll('option[data-type]');
    sel.value = '';
    opts.forEach(opt => {
        if (opt.dataset.type === type) {
            opt.classList.remove('hidden');
            opt.removeAttribute('disabled');
        } else {
            opt.classList.add('hidden');
            opt.setAttribute('disabled', 'disabled');
        }
    });
}

// Init on load
document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('input[name="type"]:checked');
    if (checked) updateCategories(checked.value);
});
</script>
@endsection
