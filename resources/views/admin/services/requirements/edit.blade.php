@extends('layouts.app')

@section('title', 'Edit Requirement — ' . $service->name)
@section('page-title', 'Edit Requirement Template')

@section('content')
<div class="py-6 max-w-2xl">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.services.requirements.index', $service) }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Requirement</h1>
            <p class="text-sm text-gray-500">For: <strong>{{ $service->name }}</strong></p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.services.requirements.update', [$service, $requirement]) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Requirement Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $requirement->name) }}" required
                       class="form-input w-full @error('name') border-red-400 @enderror">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Description / Instructions</label>
                <textarea name="description" rows="2" class="form-input w-full">{{ old('description', $requirement->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Type <span class="text-red-500">*</span></label>
                    <select name="type" id="req-type" required class="form-select w-full" onchange="toggleFileTypes()">
                        <option value="file"     @selected(old('type',$requirement->type)==='file')>File Upload</option>
                        <option value="checkbox" @selected(old('type',$requirement->type)==='checkbox')>Checkbox Declaration</option>
                        <option value="text"     @selected(old('type',$requirement->type)==='text')>Text Entry</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $requirement->sort_order) }}" min="0" class="form-input w-full">
                </div>
            </div>

            <div id="file-types-row">
                <label class="form-label">Accepted File Types</label>
                <input type="text" name="accepted_file_types"
                       value="{{ old('accepted_file_types', $requirement->accepted_file_types) }}"
                       class="form-input w-full" placeholder="pdf,jpg,jpeg,png">
                <p class="text-xs text-gray-400 mt-1">Comma-separated extensions</p>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_required" value="1" class="rounded border-gray-300 text-blue-600"
                           @checked(old('is_required', $requirement->is_required))>
                    <span class="text-sm text-gray-700 font-medium">Mandatory</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-blue-600"
                           @checked(old('is_active', $requirement->is_active))>
                    <span class="text-sm text-gray-700 font-medium">Active</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.services.requirements.index', $service) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
function toggleFileTypes() {
    const t = document.getElementById('req-type').value;
    document.getElementById('file-types-row').style.display = t === 'file' ? '' : 'none';
}
toggleFileTypes();
</script>
@endpush
@endsection
