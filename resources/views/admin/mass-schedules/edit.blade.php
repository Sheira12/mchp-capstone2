@extends('layouts.app')

@section('title', 'Edit Mass Schedule')
@section('page-title', 'Edit Mass Schedule')

@section('content')
<div class="py-6 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.mass-schedules.update', $massSchedule) }}" class="space-y-4">
            @csrf @method('PUT')

            @if($massSchedule->special_date)
            <div class="space-y-4">
                <div>
                    <label class="form-label">Special Date</label>
                    <input type="date" name="special_date"
                           value="{{ old('special_date', $massSchedule->special_date) }}"
                           class="form-input w-full">
                </div>
                <div>
                    <label class="form-label">Special Title</label>
                    <input type="text" name="special_title"
                           value="{{ old('special_title', $massSchedule->special_title) }}"
                           class="form-input w-full">
                </div>
            </div>
            @else
            <div>
                <label class="form-label">Day of Week</label>
                <select name="day_of_week" class="form-select w-full">
                    <option value="">Select day…</option>
                    @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $i => $day)
                    <option value="{{ $i }}" @selected(old('day_of_week', $massSchedule->day_of_week) == $i)>{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div>
                <label class="form-label">Time <span class="text-red-500">*</span></label>
                <input type="time" name="time" value="{{ old('time', $massSchedule->time) }}" required class="form-input w-full">
            </div>

            <div>
                <label class="form-label">Language <span class="text-red-500">*</span></label>
                <select name="language" required class="form-select w-full">
                    <option value="Filipino" @selected(old('language', $massSchedule->language) === 'Filipino')>Filipino</option>
                    <option value="English" @selected(old('language', $massSchedule->language) === 'English')>English</option>
                    <option value="Filipino/English" @selected(old('language', $massSchedule->language) === 'Filipino/English')>Filipino/English</option>
                </select>
            </div>

            <div>
                <label class="form-label">Celebrant</label>
                <input type="text" name="celebrant" value="{{ old('celebrant', $massSchedule->celebrant) }}"
                       class="form-input w-full">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       class="rounded border-gray-300 text-blue-600" @checked(old('is_active', $massSchedule->is_active))>
                <label for="is_active" class="text-sm text-gray-700">Active</label>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-input w-full">{{ old('notes', $massSchedule->notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.mass-schedules.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
