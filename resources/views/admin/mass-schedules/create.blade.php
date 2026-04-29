@extends('layouts.app')

@section('title', 'Add Mass Schedule')
@section('page-title', 'Add Mass Schedule')

@section('content')
<div class="py-6 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.mass-schedules.store') }}" class="space-y-4">
            @csrf

            <div class="flex items-center gap-2 mb-2">
                <input type="checkbox" id="is_special" class="rounded border-gray-300 text-blue-600"
                       onchange="toggleSpecial(this.checked)">
                <label for="is_special" class="text-sm text-gray-700">This is a special/one-time Mass</label>
            </div>

            <div id="regular-fields">
                <label class="form-label">Day of Week</label>
                <select name="day_of_week" class="form-select w-full">
                    <option value="">Select day…</option>
                    <option value="0" @selected(old('day_of_week') == '0')>Sunday</option>
                    <option value="1" @selected(old('day_of_week') == '1')>Monday</option>
                    <option value="2" @selected(old('day_of_week') == '2')>Tuesday</option>
                    <option value="3" @selected(old('day_of_week') == '3')>Wednesday</option>
                    <option value="4" @selected(old('day_of_week') == '4')>Thursday</option>
                    <option value="5" @selected(old('day_of_week') == '5')>Friday</option>
                    <option value="6" @selected(old('day_of_week') == '6')>Saturday</option>
                </select>
            </div>

            <div id="special-fields" class="hidden space-y-4">
                <div>
                    <label class="form-label">Special Date</label>
                    <input type="date" name="special_date" value="{{ old('special_date') }}" class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Special Title</label>
                    <input type="text" name="special_title" value="{{ old('special_title') }}"
                           class="form-input w-full" placeholder="e.g. Christmas Midnight Mass">
                </div>
            </div>

            <div>
                <label class="form-label">Time <span class="text-red-500">*</span></label>
                <input type="time" name="time" value="{{ old('time') }}" required class="form-input w-full">
                @error('time')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Language <span class="text-red-500">*</span></label>
                <select name="language" required class="form-select w-full">
                    <option value="Filipino" @selected(old('language') === 'Filipino')>Filipino</option>
                    <option value="English" @selected(old('language') === 'English')>English</option>
                    <option value="Filipino/English" @selected(old('language') === 'Filipino/English')>Filipino/English</option>
                </select>
            </div>

            <div>
                <label class="form-label">Celebrant</label>
                <input type="text" name="celebrant" value="{{ old('celebrant', config('parish.priest')) }}"
                       class="form-input w-full">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       class="rounded border-gray-300 text-blue-600" @checked(old('is_active', true))>
                <label for="is_active" class="text-sm text-gray-700">Active</label>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input w-full">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Add Schedule</button>
                <a href="{{ route('admin.mass-schedules.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSpecial(isSpecial) {
    document.getElementById('regular-fields').classList.toggle('hidden', isSpecial);
    document.getElementById('special-fields').classList.toggle('hidden', !isSpecial);
}
</script>
@endpush
