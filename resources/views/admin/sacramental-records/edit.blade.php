@extends('layouts.app')

@section('title', 'Edit Sacramental Record')
@section('page-title', 'Edit Sacramental Record')

@section('content')
<div class="py-6 max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.sacramental-records.update', $sacramentalRecord) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Parishioner</label>
                <div class="form-input bg-gray-50 text-gray-700">{{ $sacramentalRecord->parishioner->full_name }}</div>
                <input type="hidden" name="parishioner_id" value="{{ $sacramentalRecord->parishioner_id }}">
            </div>

            <div>
                <label class="form-label">Sacrament Type <span class="text-red-500">*</span></label>
                <select name="type" required class="form-select w-full" id="sacrament-type">
                    <option value="baptism" @selected($sacramentalRecord->type === 'baptism')>Baptism</option>
                    <option value="first_communion" @selected($sacramentalRecord->type === 'first_communion')>First Communion</option>
                    <option value="confirmation" @selected($sacramentalRecord->type === 'confirmation')>Confirmation</option>
                    <option value="marriage" @selected($sacramentalRecord->type === 'marriage')>Marriage</option>
                    <option value="death_burial" @selected($sacramentalRecord->type === 'death_burial')>Death/Burial</option>
                </select>
            </div>

            <div id="spouse-field" class="{{ $sacramentalRecord->type !== 'marriage' ? 'hidden' : '' }}">
                <label class="form-label">Spouse Parishioner ID</label>
                <input type="text" name="spouse_parishioner_id" value="{{ old('spouse_parishioner_id', $sacramentalRecord->spouse_parishioner_id) }}"
                       class="form-input w-full" placeholder="Parishioner ID">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date Administered <span class="text-red-500">*</span></label>
                    <input type="date" name="date_administered"
                           value="{{ old('date_administered', $sacramentalRecord->date_administered->format('Y-m-d')) }}"
                           required class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Celebrant <span class="text-red-500">*</span></label>
                    <input type="text" name="celebrant" value="{{ old('celebrant', $sacramentalRecord->celebrant) }}"
                           required class="form-input w-full">
                </div>
            </div>

            <div>
                <label class="form-label">Venue</label>
                <input type="text" name="venue" value="{{ old('venue', $sacramentalRecord->venue) }}"
                       class="form-input w-full">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Register Number</label>
                    <input type="text" name="register_number" value="{{ old('register_number', $sacramentalRecord->register_number) }}"
                           class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Page Number</label>
                    <input type="text" name="page_number" value="{{ old('page_number', $sacramentalRecord->page_number) }}"
                           class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Line Number</label>
                    <input type="text" name="line_number" value="{{ old('line_number', $sacramentalRecord->line_number) }}"
                           class="form-input w-full">
                </div>
            </div>

            <div>
                <label class="form-label">Godparents / Sponsors</label>
                <div id="godparents-list" class="space-y-2">
                    @foreach(old('godparents', $sacramentalRecord->godparents ?? []) as $gp)
                    <div class="flex gap-2">
                        <input type="text" name="godparents[]" value="{{ $gp }}"
                               class="form-input flex-1" placeholder="Full name">
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 px-2">✕</button>
                    </div>
                    @endforeach
                </div>
                <button type="button" onclick="addGodparent()" class="mt-2 text-sm text-blue-600 hover:underline">+ Add godparent</button>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-input w-full">{{ old('notes', $sacramentalRecord->notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.sacramental-records.show', $sacramentalRecord) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('sacrament-type')?.addEventListener('change', function() {
    document.getElementById('spouse-field').classList.toggle('hidden', this.value !== 'marriage');
});

function addGodparent() {
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = '<input type="text" name="godparents[]" class="form-input flex-1" placeholder="Full name"><button type="button" onclick="this.parentElement.remove()" class="text-red-500 px-2">✕</button>';
    document.getElementById('godparents-list').appendChild(div);
}
</script>
@endpush
