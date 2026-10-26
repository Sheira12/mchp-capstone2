{{-- Shared form partial for create/edit --}}
@php
    $pkg = $package ?? null;
    // Build JS-safe initial inclusions array
    $initInclusions = [];
    if ($pkg) {
        foreach ($pkg->inclusionsList() as $inc) {
            $initInclusions[] = [
                'name'     => $inc['name'],
                'price'    => $inc['price'],
                'qty'      => $inc['qty'],
                'subtotal' => $inc['subtotal'],
            ];
        }
    }
    if (old('inclusions_json')) {
        $decoded = json_decode(old('inclusions_json'), true);
        if (is_array($decoded)) $initInclusions = $decoded;
    }
@endphp

<div>
    <label class="form-label">Service <span class="text-red-500">*</span></label>
    <select name="service_id" required class="form-select w-full @error('service_id') border-red-400 @enderror">
        <option value="">Select service…</option>
        @foreach($services as $svc)
        <option value="{{ $svc->id }}" @selected(old('service_id', $pkg?->service_id) == $svc->id)>{{ $svc->name }}</option>
        @endforeach
    </select>
    @error('service_id')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label">Package Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name', $pkg?->name) }}" required
           class="form-input w-full @error('name') border-red-400 @enderror"
           placeholder="e.g. Standard Package, Premium Package">
    @error('name')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label">Description</label>
    <textarea name="description" rows="2" class="form-input w-full"
              placeholder="Brief description shown to parishioners…">{{ old('description', $pkg?->description) }}</textarea>
</div>

{{-- ── INCLUSIONS TABLE ─────────────────────────────────────────────────── --}}
<div>
    <div class="flex items-center justify-between mb-2">
        <label class="form-label mb-0">
            Inclusions
            <span class="text-gray-400 font-normal text-xs ml-1">— add each item with its individual price</span>
        </label>
        <button type="button" onclick="addInclusionRow()"
                class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Item
        </button>
    </div>

    <div class="border border-gray-200 rounded-xl overflow-hidden">
        <table class="min-w-full text-sm" id="inclusions-table">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600 w-5/12">Inclusion Name</th>
                    <th class="px-3 py-2 text-right font-semibold text-gray-600 w-2/12">Unit Price (₱)</th>
                    <th class="px-3 py-2 text-right font-semibold text-gray-600 w-1/12">Qty</th>
                    <th class="px-3 py-2 text-right font-semibold text-gray-600 w-2/12">Subtotal</th>
                    <th class="px-3 py-2 w-1/12"></th>
                </tr>
            </thead>
            <tbody id="inclusions-body">
                {{-- Rows injected by JS --}}
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                    <td colspan="3" class="px-3 py-2 text-right text-xs font-bold text-gray-500 uppercase tracking-wide">
                        Inclusions Total
                    </td>
                    <td class="px-3 py-2 text-right font-bold text-gray-900" id="inclusions-total-display">₱0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <p class="text-xs text-gray-400 mt-1">The package price below will update automatically as you add inclusions. You may override it manually.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <label class="form-label">Discount (₱) <span class="text-gray-400 font-normal text-xs">(optional)</span></label>
        <input type="number" name="discount" id="discount-input"
               value="{{ old('discount', $pkg?->discount ?? '') }}"
               step="0.01" min="0"
               class="form-input w-full"
               placeholder="0.00"
               oninput="recalcTotal()">
        <p class="text-xs text-gray-400 mt-0.5">Deducted from inclusions total.</p>
    </div>
    <div>
        <label class="form-label">Final Package Price (₱) <span class="text-red-500">*</span></label>
        <input type="number" name="price" id="price-input"
               value="{{ old('price', $pkg?->price) }}" required step="0.01" min="0"
               class="form-input w-full @error('price') border-red-400 @enderror"
               placeholder="Auto-calculated">
        @error('price')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $pkg?->sort_order ?? 0) }}" min="0"
               class="form-input w-full">
    </div>
</div>

{{-- Hidden field carries the rich inclusions JSON to the controller --}}
<input type="hidden" name="inclusions_json" id="inclusions-json-input" value="{{ old('inclusions_json', json_encode($initInclusions, JSON_UNESCAPED_UNICODE)) }}">

<div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded border-gray-300 text-blue-600"
           @checked(old('is_active', $pkg?->is_active ?? true))>
    <label for="is_active" class="text-sm text-gray-700 font-medium">Package is active and visible to parishioners</label>
</div>

@push('scripts')
<script>
// ── Initial data from PHP ────────────────────────────────────────────────────
let inclusions = @json($initInclusions);

// ── Bootstrap ────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    if (inclusions.length === 0) {
        // Start with one empty row for new packages
        inclusions = [{ name: '', price: 0, qty: 1, subtotal: 0 }];
    }
    renderRows();
});

// ── Render all rows ───────────────────────────────────────────────────────────
function renderRows() {
    const tbody = document.getElementById('inclusions-body');
    tbody.innerHTML = '';
    inclusions.forEach((inc, idx) => {
        tbody.appendChild(makeRow(inc, idx));
    });
    recalcTotal();
}

// ── Build a single table row ──────────────────────────────────────────────────
function makeRow(inc, idx) {
    const tr = document.createElement('tr');
    tr.className = 'border-b border-gray-100 last:border-0 hover:bg-gray-50';
    tr.innerHTML = `
        <td class="px-2 py-1.5">
            <input type="text"
                   class="form-input w-full text-sm"
                   placeholder="e.g. Baptismal outfit"
                   value="${escHtml(inc.name)}"
                   oninput="updateRow(${idx}, 'name', this.value)">
        </td>
        <td class="px-2 py-1.5">
            <input type="number" min="0" step="0.01"
                   class="form-input w-full text-sm text-right"
                   placeholder="0.00"
                   value="${inc.price}"
                   oninput="updateRow(${idx}, 'price', parseFloat(this.value)||0)">
        </td>
        <td class="px-2 py-1.5">
            <input type="number" min="1" step="1"
                   class="form-input w-full text-sm text-right"
                   value="${inc.qty}"
                   oninput="updateRow(${idx}, 'qty', parseInt(this.value)||1)">
        </td>
        <td class="px-2 py-1.5 text-right font-semibold text-gray-700" id="subtotal-${idx}">
            ₱${fmt(inc.subtotal)}
        </td>
        <td class="px-2 py-1.5 text-center">
            <button type="button" onclick="removeRow(${idx})"
                    class="text-red-400 hover:text-red-600 transition"
                    title="Remove">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </td>`;
    return tr;
}

// ── Update a field on a row ───────────────────────────────────────────────────
function updateRow(idx, field, val) {
    inclusions[idx][field] = val;
    const qty      = Math.max(1, parseInt(inclusions[idx].qty) || 1);
    const price    = Math.max(0, parseFloat(inclusions[idx].price) || 0);
    inclusions[idx].subtotal = Math.round(qty * price * 100) / 100;
    // Update subtotal cell in-place without full re-render (preserves focus)
    const cell = document.getElementById('subtotal-' + idx);
    if (cell) cell.textContent = '₱' + fmt(inclusions[idx].subtotal);
    recalcTotal();
}

// ── Add a new empty row ───────────────────────────────────────────────────────
function addInclusionRow() {
    inclusions.push({ name: '', price: 0, qty: 1, subtotal: 0 });
    const tbody = document.getElementById('inclusions-body');
    tbody.appendChild(makeRow(inclusions[inclusions.length - 1], inclusions.length - 1));
    recalcTotal();
}

// ── Remove a row ──────────────────────────────────────────────────────────────
function removeRow(idx) {
    inclusions.splice(idx, 1);
    renderRows(); // full re-render to fix indices
}

// ── Recalculate totals and update price/discount fields ───────────────────────
function recalcTotal() {
    const subTotal = inclusions.reduce((s, i) => s + (parseFloat(i.subtotal) || 0), 0);
    const discount = Math.max(0, parseFloat(document.getElementById('discount-input').value) || 0);
    const finalPrice = Math.max(0, subTotal - discount);

    document.getElementById('inclusions-total-display').textContent = '₱' + fmt(subTotal);

    // Auto-fill price field from inclusions (only if user hasn't manually typed a different value)
    const priceInput = document.getElementById('price-input');
    priceInput.value = fmt(finalPrice);

    // Sync hidden JSON field
    document.getElementById('inclusions-json-input').value = JSON.stringify(
        inclusions.filter(i => i.name.trim() !== '')
    );
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmt(n) {
    return parseFloat(n || 0).toFixed(2);
}
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
@endpush
