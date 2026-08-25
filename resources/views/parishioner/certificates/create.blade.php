@extends('layouts.portal')
@section('title', 'Request Certificate')

@section('content')
<div class="py-6 max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.certificates.index') }}"
           class="w-9 h-9 rounded-xl bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition shadow-sm">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">Request a Certificate</h1>
            <p class="text-sm text-gray-500">Submit your request and the parish office will process it within 1–3 working days.</p>
        </div>
    </div>

    {{-- Duplicate warning --}}
    @if(session('duplicate_warning'))
    @php $dup = session('duplicate_warning'); @endphp
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-amber-900 text-sm">You already have a {{ $dup->getTypeLabel() }} on record</p>
                <p class="text-xs text-amber-700 mt-1">
                    Certificate #{{ $dup->certificate_number }} — Status: {{ ucfirst($dup->status) }}.
                    Do you still want to request a new one?
                </p>
                <form method="POST" action="{{ route('parishioner.certificates.store') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="type" value="{{ old('type') }}">
                    <input type="hidden" name="sacramental_record_id" value="{{ old('sacramental_record_id') }}">
                    <input type="hidden" name="purpose" value="{{ old('purpose') }}">
                    <input type="hidden" name="notes" value="{{ old('notes') }}">
                    <input type="hidden" name="confirm_duplicate" value="1">
                    <button type="submit" class="text-sm font-bold text-amber-800 underline hover:text-amber-900">
                        Yes, submit a new request anyway
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Info banner --}}
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="text-sm text-blue-800">
            <p class="font-semibold mb-1">Certificate Fee: ₱100.00 per certificate</p>
            <p class="text-blue-700">Payment can be made at the parish office or online once your request is approved. Processing takes 1–3 working days.</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('parishioner.certificates.store') }}" method="POST" class="space-y-5">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-5">

            {{-- Certificate Type --}}
            <div>
                <label class="form-label font-semibold">Certificate Type <span class="text-red-500">*</span></label>
                <select name="type" id="cert-type"
                        class="form-select mt-1 @error('type') border-red-400 @enderror"
                        onchange="updateRecordFilter(this.value)" required>
                    <option value="">Select certificate type...</option>
                    @foreach($certificateTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<p class="form-error mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Sacramental Record (optional) --}}
            @if($sacramentalRecords->count())
            <div id="record-section">
                <label class="form-label font-semibold">Linked Sacramental Record <span class="text-gray-400 font-normal">(optional)</span></label>
                <p class="text-xs text-gray-500 mb-2">Select the record this certificate is based on, if applicable.</p>
                <select name="sacramental_record_id" class="form-select @error('sacramental_record_id') border-red-400 @enderror">
                    <option value="">None / Not applicable</option>
                    @foreach($sacramentalRecords as $record)
                    <option value="{{ $record->id }}"
                            data-type="{{ $record->type }}"
                            {{ old('sacramental_record_id') == $record->id ? 'selected' : '' }}>
                        {{ $record->getTypeLabel() }} — {{ $record->date_administered->format('M d, Y') }}
                        @if($record->celebrant) ({{ $record->celebrant }})@endif
                    </option>
                    @endforeach
                </select>
                @error('sacramental_record_id')<p class="form-error mt-1">{{ $message }}</p>@enderror
            </div>
            @endif

            {{-- Purpose --}}
            <div>
                <label class="form-label font-semibold">Purpose <span class="text-red-500">*</span></label>
                <p class="text-xs text-gray-500 mb-2">Why do you need this certificate? (e.g., school requirement, employment, travel)</p>
                <input type="text" name="purpose" value="{{ old('purpose') }}"
                       placeholder="e.g., School enrollment requirement"
                       class="form-input @error('purpose') border-red-400 @enderror" required>
                @error('purpose')<p class="form-error mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Additional Notes --}}
            <div>
                <label class="form-label font-semibold">Additional Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="notes" rows="3" class="form-input w-full"
                          placeholder="Any special instructions or additional information for the parish office...">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Parishioner Info Summary --}}
        <div class="bg-gray-50 rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Your Information</p>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-gray-500 text-xs">Full Name</p>
                    <p class="font-semibold text-gray-900">{{ $parishioner->full_name }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Contact</p>
                    <p class="font-semibold text-gray-900">{{ $parishioner->contact_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Email</p>
                    <p class="font-semibold text-gray-900">{{ $parishioner->email ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">Barangay</p>
                    <p class="font-semibold text-gray-900">{{ $parishioner->barangay ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center gap-3">
            <button type="submit" id="cert-submit-btn"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-blue-600 text-white font-bold px-8 py-3 rounded-xl hover:bg-blue-700 shadow-md transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Submit Request
            </button>
            <a href="{{ route('parishioner.certificates.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
// Map certificate types to sacramental record types for smart filtering
const typeMap = {
    'baptism':         'baptism',
    'confirmation':    'confirmation',
    'marriage':        'marriage',
    'first_communion': 'first_communion',
    'death_burial':    'death_burial',
};

function updateRecordFilter(certType) {
    const select = document.querySelector('select[name="sacramental_record_id"]');
    if (!select) return;

    const relatedType = typeMap[certType];
    const options = select.querySelectorAll('option[data-type]');

    options.forEach(opt => {
        if (!relatedType || opt.dataset.type === relatedType) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });

    // Reset selection if current selection is now hidden
    const selected = select.options[select.selectedIndex];
    if (selected && selected.style.display === 'none') {
        select.value = '';
    }
}

// Run on page load if type is pre-selected (e.g., after validation error)
document.addEventListener('DOMContentLoaded', () => {
    const certType = document.getElementById('cert-type');
    if (certType && certType.value) {
        updateRecordFilter(certType.value);
    }

    // Disable submit button on form submit to prevent double-submission
    const form = document.querySelector('form[action="{{ route('parishioner.certificates.store') }}"]');
    const submitBtn = document.getElementById('cert-submit-btn');
    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            submitBtn.innerHTML =
                '<svg style="width:16px;height:16px;animation:spin 1s linear infinite;flex-shrink:0" fill="none" viewBox="0 0 24 24">' +
                '<circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>' +
                '<path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/>' +
                '</svg> Submitting…';
        });
    }
});
</script>

@push('styles')
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endpush
