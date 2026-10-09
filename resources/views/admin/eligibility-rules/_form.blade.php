{{-- Shared form partial for create/edit eligibility rules --}}
@php $rule = $eligibilityRule ?? null; @endphp

<div>
    <label class="form-label">Criterion Type <span class="text-red-500">*</span></label>
    <select name="criterion_type" id="criterion-type" required class="form-select w-full"
            onchange="updateParamFields()">
        <option value="">Select type…</option>
        @foreach(\App\Models\EligibilityRule::CRITERION_TYPES as $val => $label)
        <option value="{{ $val }}" @selected(old('criterion_type', $rule?->criterion_type) === $val)>{{ $label }}</option>
        @endforeach
    </select>
    @error('criterion_type')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label">Applies To <span class="text-red-500">*</span></label>
    <select name="applies_to" required class="form-select w-full">
        @foreach(\App\Models\EligibilityRule::APPLIES_TO_LABELS as $val => $label)
        <option value="{{ $val }}" @selected(old('applies_to', $rule?->applies_to ?? 'applicant') === $val)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div>
    <label class="form-label">Rule Name <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name', $rule?->name) }}" required
           class="form-input w-full @error('name') border-red-400 @enderror"
           placeholder="e.g. Pre-Baptismal Seminar (Parents)">
    @error('name')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label">Description / Note to Parishioner</label>
    <textarea name="description" rows="2" class="form-input w-full"
              placeholder="Explanation shown to the parishioner when this rule is not met…">{{ old('description', $rule?->description) }}</textarea>
</div>

{{-- ── Dynamic param fields ── --}}
<div id="params-seminar_completed" class="param-group space-y-4">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Seminar Parameters</p>
    <div>
        <label class="form-label">Seminar Service Slug</label>
        <input type="text" name="param_service_slug" value="{{ old('param_service_slug', $rule?->param('service_slug')) }}"
               class="form-input w-full" placeholder="e.g. pre_baptismal, pre_marriage">
        <p class="text-xs text-gray-400 mt-1">Must match a service slug. Leave blank if any completed seminar counts.</p>
    </div>
    <div>
        <label class="form-label">Validity (days) — optional</label>
        <input type="number" name="param_validity_days" value="{{ old('param_validity_days', $rule?->param('validity_days')) }}"
               class="form-input w-full" min="1" placeholder="e.g. 365 — leave blank for no expiry">
    </div>
</div>

<div id="params-document_approved" class="param-group space-y-4 hidden">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Document Parameters</p>
    <div>
        <label class="form-label">Document Key / Name</label>
        <input type="text" name="param_document_key" value="{{ old('param_document_key', $rule?->param('document_key')) }}"
               class="form-input w-full" placeholder="e.g. Baptismal Certificate, Civil Marriage License">
        <p class="text-xs text-gray-400 mt-1">Matched against booking requirement names (partial match).</p>
    </div>
</div>

<div id="params-prerequisite_sacrament" class="param-group space-y-4 hidden">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Sacrament Parameters</p>
    <div>
        <label class="form-label">Required Sacrament Type</label>
        <select name="param_sacrament_type" class="form-select w-full">
            <option value="">Select sacrament…</option>
            @foreach(['baptism'=>'Baptism','confirmation'=>'Confirmation','first_communion'=>'First Communion','marriage'=>'Marriage','death_burial'=>'Death/Burial'] as $val => $lbl)
            <option value="{{ $val }}" @selected(old('param_sacrament_type', $rule?->param('sacrament_type')) === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
    </div>
</div>

<div id="params-minimum_age" class="param-group space-y-4 hidden">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Age Parameters</p>
    <div>
        <label class="form-label">Minimum Age (years)</label>
        <input type="number" name="param_min_age" value="{{ old('param_min_age', $rule?->param('min_age')) }}"
               class="form-input w-full" min="0" max="120" placeholder="e.g. 13">
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_required" id="is_required" value="1"
               class="rounded border-gray-300 text-blue-600"
               @checked(old('is_required', $rule?->is_required ?? true))>
        <label for="is_required" class="text-sm text-gray-700 font-medium">This rule is <strong>required</strong> (blocks booking if not met)</label>
    </div>
    @if($rule)
    <div class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="is_active" value="1"
               class="rounded border-gray-300 text-blue-600"
               @checked(old('is_active', $rule->is_active))>
        <label for="is_active" class="text-sm text-gray-700 font-medium">Active</label>
    </div>
    @endif
</div>

<div>
    <label class="form-label">Sort Order</label>
    <input type="number" name="sort_order" value="{{ old('sort_order', $rule?->sort_order ?? 0) }}"
           min="0" class="form-input w-full">
</div>

@push('scripts')
<script>
function updateParamFields() {
    const type = document.getElementById('criterion-type').value;
    document.querySelectorAll('.param-group').forEach(el => el.classList.add('hidden'));
    const target = document.getElementById('params-' + type);
    if (target) target.classList.remove('hidden');
}
document.addEventListener('DOMContentLoaded', updateParamFields);
</script>
@endpush
