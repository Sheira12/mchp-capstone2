@extends('layouts.app')
@section('title', 'Edit Certificate — ' . $certificate->certificate_number)
@section('page-title', 'Edit Certificate')

@section('content')
<div class="py-6 max-w-2xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.certificates.show', $certificate) }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Certificate</h1>
            <p class="text-sm text-gray-500 font-mono">{{ $certificate->certificate_number }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        {{-- Parishioner (read-only) --}}
        <div class="mb-5 bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                {{ substr($certificate->parishioner->first_name, 0, 1) }}
            </div>
            <div>
                <p class="font-bold text-blue-900">{{ $certificate->parishioner->full_name }}</p>
                <p class="text-xs text-blue-500">{{ $certificate->parishioner->contact_number ?? 'No contact' }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.certificates.update', $certificate) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Certificate Type <span class="text-red-500">*</span></label>
                <select name="type" required class="form-select w-full @error('type') border-red-400 @enderror">
                    @foreach(\App\Models\Certificate::TYPES as $val => $label)
                    <option value="{{ $val }}" @selected(old('type', $certificate->type) === $val)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Issued Date <span class="text-red-500">*</span></label>
                <input type="date" name="issued_date"
                       value="{{ old('issued_date', $certificate->issued_date->format('Y-m-d')) }}"
                       required class="form-input w-full @error('issued_date') border-red-400 @enderror">
                @error('issued_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" required class="form-select w-full @error('status') border-red-400 @enderror">
                    <option value="draft"    @selected(old('status', $certificate->status) === 'draft')>Draft</option>
                    <option value="issued"   @selected(old('status', $certificate->status) === 'issued')>Issued</option>
                    <option value="released" @selected(old('status', $certificate->status) === 'released')>Released</option>
                    <option value="revoked"  @selected(old('status', $certificate->status) === 'revoked')>Revoked</option>
                </select>
                @error('status')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Purpose</label>
                <input type="text" name="purpose"
                       value="{{ old('purpose', $certificate->purpose) }}"
                       class="form-input w-full"
                       placeholder="e.g. For school enrollment, civil registration…">
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input w-full"
                          placeholder="Internal notes…">{{ old('notes', $certificate->notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.certificates.show', $certificate) }}" class="btn-secondary">Cancel</a>
                <form action="{{ route('admin.certificates.destroy', $certificate) }}" method="POST"
                      class="ml-auto" onsubmit="return confirm('Delete this certificate? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50">Delete</button>
                </form>
            </div>
        </form>
    </div>

</div>
@endsection
