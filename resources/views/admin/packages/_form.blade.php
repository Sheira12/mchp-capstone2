{{-- Shared form partial for create/edit --}}
@php $pkg = $package ?? null; @endphp

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

<div>
    <label class="form-label">Inclusions <span class="text-gray-400 font-normal">(one item per line)</span></label>
    <textarea name="inclusions" rows="6" class="form-input w-full font-mono text-sm"
              placeholder="Church ceremony (90 minutes)&#10;Basic floral arrangement&#10;Sound system">{{ old('inclusions', $pkg ? implode("\n", $pkg->inclusionsList()) : '') }}</textarea>
    <p class="text-xs text-gray-400 mt-1">Each line becomes a bullet point in the order of payment.</p>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="form-label">Price (₱) <span class="text-red-500">*</span></label>
        <input type="number" name="price" value="{{ old('price', $pkg?->price) }}" required step="0.01" min="0"
               class="form-input w-full @error('price') border-red-400 @enderror"
               placeholder="0.00">
        @error('price')<p class="form-error">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="form-label">Sort Order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $pkg?->sort_order ?? 0) }}" min="0"
               class="form-input w-full">
    </div>
</div>

<div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded border-gray-300 text-blue-600"
           @checked(old('is_active', $pkg?->is_active ?? true))>
    <label for="is_active" class="text-sm text-gray-700 font-medium">Package is active and visible to parishioners</label>
</div>
