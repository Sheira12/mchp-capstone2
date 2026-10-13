{{-- Shared form partial for service requirement create / edit --}}
@php $req = $requirement ?? null; @endphp

<div>
    <label class="form-label">Requirement Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name', $req?->name) }}" required
           class="form-input w-full @error('name') border-red-400 @enderror"
           placeholder="e.g. Pre-Baptismal Seminar, Birth Certificate, Baptism Record">
    @error('name')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label">Description / Instructions for Parishioner</label>
    <textarea name="description" rows="2" class="form-input w-full"
              placeholder="Helper text shown to the parishioner — what is this requirement and why it's needed…">{{ old('description', $req?->description) }}</textarea>
</div>

{{-- ── Eligibility Type (v2 gate) ── --}}
<div>
    <label class="form-label">Eligibility Type <span class="text-red-500">*</span>
        <span class="text-xs text-gray-400 font-normal ml-1">— determines how this requirement is satisfied</span>
    </label>
    <select name="eligibility_type" id="elig-type" required class="form-select w-full"
            onchange="toggleEligFields()">
        @foreach(\App\Models\ServiceRequirement::ELIGIBILITY_TYPES as $val => $label)
        <option value="{{ $val }}" @selected(old('eligibility_type', $req?->eligibility_type ?? $req?->type ?? 'document') === $val)>
            {{ $label }}
        </option>
        @endforeach
    </select>
    @error('eligibility_type')<p class="form-error">{{ $message }}</p>@enderror
    <div class="mt-2 text-xs text-gray-500 space-y-0.5">
        <p><strong>Seminar</strong> — parishioner attended a seminar (registered + attended in system, OR self-declared)</p>
        <p><strong>Document</strong> — parishioner submits a document for staff review</p>
        <p><strong>Sacramental Record</strong> — automatically satisfied if a matching record exists in the system; parishioner can also declare it from another parish</p>
    </div>
</div>

{{-- Sacrament type (shown only for sacrament_record) --}}
<div id="sacrament-row" class="{{ old('eligibility_type', $req?->eligibility_type ?? $req?->type) === 'sacrament_record' ? '' : 'hidden' }}">
    <label class="form-label">Required Sacrament Type</label>
    <select name="required_sacrament_type" class="form-select w-full">
        <option value="">Select sacrament…</option>
        @foreach(\App\Models\ServiceRequirement::SACRAMENT_TYPES as $val => $label)
        <option value="{{ $val }}" @selected(old('required_sacrament_type', $req?->required_sacrament_type) === $val)>{{ $label }}</option>
        @endforeach
    </select>
    @error('required_sacrament_type')<p class="form-error">{{ $message }}</p>@enderror
</div>

{{-- Accepted file types (shown for file / document types) --}}
<div id="file-types-row" class="{{ in_array(old('eligibility_type', $req?->eligibility_type ?? $req?->type), ['file','document']) ? '' : 'hidden' }}">
    <label class="form-label">Accepted File Types</label>
    <input type="text" name="accepted_file_types"
           value="{{ old('accepted_file_types', $req?->accepted_file_types ?? 'pdf,jpg,jpeg,png') }}"
           class="form-input w-full" placeholder="pdf,jpg,jpeg,png">
    <p class="text-xs text-gray-400 mt-1">Comma-separated extensions shown to the parishioner.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $req?->sort_order ?? 0) }}" min="0" class="form-input w-full">
    </div>
</div>

<div class="flex flex-wrap items-center gap-6 pt-1">
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_required" value="1" class="rounded border-gray-300 text-blue-600"
               @checked(old('is_required', $req?->is_required ?? true))>
        <span class="text-sm text-gray-700 font-medium">
            <strong>Mandatory</strong> — blocks booking if not satisfied
        </span>
    </label>
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="requires_proof" value="1" class="rounded border-gray-300 text-blue-600"
               @checked(old('requires_proof', $req?->requires_proof ?? true))>
        <span class="text-sm text-gray-700 font-medium">Proof upload required</span>
    </label>
    @if($req)
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-blue-600"
               @checked(old('is_active', $req->is_active))>
        <span class="text-sm text-gray-700 font-medium">Active</span>
    </label>
    @endif
</div>

@push('scripts')
<script>
function toggleEligFields() {
    const t          = document.getElementById('elig-type').value;
    const sacRow     = document.getElementById('sacrament-row');
    const fileRow    = document.getElementById('file-types-row');

    sacRow.classList.toggle('hidden',  t !== 'sacrament_record');
    fileRow.classList.toggle('hidden', !['file','document'].includes(t));
}
document.addEventListener('DOMContentLoaded', toggleEligFields);
</script>
@endpush
