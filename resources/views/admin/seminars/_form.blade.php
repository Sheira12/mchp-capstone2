@php $sem = $seminar ?? null; @endphp

<div>
    <label class="form-label">Service / Seminar Type <span class="text-red-500">*</span></label>
    <select name="service_id" required class="form-select w-full @error('service_id') border-red-400 @enderror">
        <option value="">Select service…</option>
        @foreach($services as $svc)
        <option value="{{ $svc->id }}" @selected(old('service_id', $sem?->service_id) == $svc->id)>{{ $svc->name }}</option>
        @endforeach
    </select>
    @error('service_id')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div>
    <label class="form-label">Title <span class="text-red-500">*</span></label>
    <input type="text" name="title" value="{{ old('title', $sem?->title) }}" required
           class="form-input w-full" placeholder="e.g. Pre-Baptismal Seminar — January 2027">
    @error('title')<p class="form-error">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="form-label">Date & Time <span class="text-red-500">*</span></label>
        <input type="datetime-local" name="scheduled_at"
               value="{{ old('scheduled_at', $sem?->scheduled_at?->format('Y-m-d\TH:i')) }}"
               required class="form-input w-full">
        @error('scheduled_at')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label">Capacity <span class="text-red-500">*</span></label>
        <input type="number" name="capacity" value="{{ old('capacity', $sem?->capacity ?? 30) }}"
               required min="1" class="form-input w-full">
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="form-label">Venue</label>
        <input type="text" name="venue" value="{{ old('venue', $sem?->venue) }}"
               class="form-input w-full" placeholder="e.g. Parish Hall, Room 2">
    </div>
    <div>
        <label class="form-label">Speaker / Facilitator</label>
        <input type="text" name="speaker" value="{{ old('speaker', $sem?->speaker) }}"
               class="form-input w-full" placeholder="e.g. Fr. Juan dela Cruz">
    </div>
</div>

@if($sem)
<div>
    <label class="form-label">Status</label>
    <select name="status" class="form-select w-full">
        @foreach(\App\Models\Seminar::STATUSES as $val => $lbl)
        <option value="{{ $val }}" @selected(old('status', $sem->status) === $val)>{{ $lbl }}</option>
        @endforeach
    </select>
</div>
@endif

<div>
    <label class="form-label">Notes</label>
    <textarea name="notes" rows="2" class="form-input w-full"
              placeholder="Additional information for attendees…">{{ old('notes', $sem?->notes) }}</textarea>
</div>
