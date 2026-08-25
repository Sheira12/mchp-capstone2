@extends('layouts.portal')

@section('title', 'My Profile')

@push('styles')
{{-- Cropper.js styles (CDN) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
/* ── Profile card components ─────────────────────── */
.profile-wrap { max-width: 680px; margin: 0 auto; width: 100%; }
.ps {
    background: #fff;
    border-radius: 1rem;
    border: 1px solid #e8edf5;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    overflow: hidden;
}
.ps-head {
    padding: 1.125rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: linear-gradient(to right, #f8faff, #f0f4ff);
}
.ps-head svg { width: 18px; height: 18px; color: #2563eb; flex-shrink: 0; }
.ps-head h2 { font-weight: 700; font-size: 0.9375rem; color: #0f172a; margin: 0; }
.ps-body { padding: 1.5rem; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.field-row.full { grid-template-columns: 1fr; }
@media(max-width:560px){ .field-row { grid-template-columns: 1fr; } }
.fl { display: flex; flex-direction: column; gap: 0.375rem; }
.fl label { font-size: 0.8125rem; font-weight: 600; color: #374151; }
.fl label span { color: #ef4444; }
.fl input, .fl select {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 0.625rem;
    font-size: 0.875rem;
    color: #0f172a;
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;
}
.fl input:focus, .fl select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
}
.fl input:disabled {
    background: #f8fafc;
    color: #94a3b8;
    cursor: not-allowed;
}
.fl .hint { font-size: 0.72rem; color: #94a3b8; margin-top: 2px; }
.fl .err  { font-size: 0.72rem; color: #ef4444; margin-top: 2px; }

/* ── Crop Modal ─────────────────────────────────── */
#crop-modal-backdrop {
    position:fixed;inset:0;z-index:9999;
    background:rgba(0,0,0,0.65);
    display:flex;align-items:center;justify-content:center;
    padding:1rem;
    opacity:0;pointer-events:none;
    transition:opacity 0.2s;
}
#crop-modal-backdrop.open { opacity:1;pointer-events:all; }
#crop-modal {
    background:#fff;border-radius:1.25rem;
    width:100%;max-width:480px;
    overflow:hidden;
    box-shadow:0 24px 64px rgba(0,0,0,0.3);
    transform:scale(0.95);transition:transform 0.2s;
}
#crop-modal-backdrop.open #crop-modal { transform:scale(1); }
.crop-modal-header {
    padding:1rem 1.25rem 0.75rem;
    border-bottom:1px solid #f1f5f9;
    display:flex;align-items:center;justify-content:space-between;
}
.crop-modal-header h3 {
    font-weight:700;font-size:1rem;color:#0f172a;margin:0;
    display:flex;align-items:center;gap:0.5rem;
}
.crop-modal-body {
    padding:1rem 1.25rem;background:#0f172a;
    max-height:65vh;display:flex;align-items:center;justify-content:center;overflow:hidden;
}
.crop-modal-body img { display:block;max-width:100%;max-height:60vh; }
.crop-modal-toolbar {
    padding:0.75rem 1.25rem;border-top:1px solid #f1f5f9;
    display:flex;align-items:center;justify-content:space-between;
    gap:0.5rem;flex-wrap:wrap;background:#f8faff;
}
.crop-tool-group { display:flex;gap:0.375rem; }
.crop-tool-btn {
    width:34px;height:34px;border-radius:8px;
    border:1.5px solid #e2e8f0;background:#fff;
    display:flex;align-items:center;justify-content:center;
    cursor:pointer;transition:all 0.15s;color:#374151;flex-shrink:0;
}
.crop-tool-btn:hover { background:#eff6ff;border-color:#93c5fd;color:#2563eb; }
.crop-modal-footer {
    padding:0.875rem 1.25rem;border-top:1px solid #f1f5f9;
    display:flex;gap:0.625rem;justify-content:flex-end;
}
#crop-preview-circle {
    width:52px;height:52px;border-radius:50%;
    overflow:hidden;border:2.5px solid #e0e7ff;
    flex-shrink:0;background:#eff6ff;margin-right:auto;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
<div class="profile-wrap space-y-5 pb-8">

    {{-- Page Header --}}
    <div>
        <h1 style="font-size:1.5rem;font-weight:800;color:#0f172a;margin:0 0 4px;">My Profile</h1>
        <p style="font-size:0.875rem;color:#64748b;">Manage your personal information and parish records</p>
    </div>

    <form action="{{ route('parishioner.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        {{-- ── Photo ── --}}
        <div class="ps">
            <div class="ps-head">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <h2>Profile Photo</h2>
            </div>
            <div class="ps-body">
                <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">

                    {{-- Avatar with click-to-change overlay --}}
                    <div style="position:relative;flex-shrink:0;" id="avatar-wrap">
                        <div id="photo-preview"
                             style="width:90px;height:90px;border-radius:50%;overflow:hidden;
                                    background:linear-gradient(135deg,#dbeafe,#e0e7ff);
                                    display:flex;align-items:center;justify-content:center;
                                    box-shadow:0 2px 12px rgba(0,0,0,0.12);
                                    border:3px solid #fff;cursor:pointer;position:relative;"
                             onclick="document.getElementById('photo-input').click()"
                             title="Click to change photo">
                            @if($parishioner?->photo_path)
                                <img id="photo-img" src="{{ Storage::url($parishioner->photo_path) }}"
                                     style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <div id="photo-placeholder" style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
                                    <svg style="width:36px;height:36px;color:#93c5fd;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            @endif
                            {{-- Hover overlay --}}
                            <div id="photo-overlay"
                                 style="position:absolute;inset:0;background:rgba(0,0,0,0.45);
                                        display:flex;flex-direction:column;align-items:center;justify-content:center;
                                        opacity:0;transition:opacity 0.2s;border-radius:50%;gap:3px;">
                                <svg style="width:18px;height:18px;color:#fff;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span style="color:#fff;font-size:0.6rem;font-weight:700;letter-spacing:0.05em;">CHANGE</span>
                            </div>
                        </div>
                    </div>

                    {{-- Controls --}}
                    <div style="flex:1;min-width:200px;">
                        {{-- Hidden real file input — feeds the crop modal --}}
                        <input type="file" name="_photo_raw" id="photo-input"
                               accept="image/jpeg,image/png,image/webp"
                               class="hidden" onchange="openCropModal(this)">

                        {{-- Hidden input that carries the cropped blob as base64 --}}
                        <input type="hidden" name="photo_cropped" id="photo-cropped-data">

                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:0.625rem;">
                            {{-- Upload / Change button --}}
                            <label for="photo-input"
                                   style="display:inline-flex;align-items:center;gap:6px;
                                          background:#2563eb;color:#fff;font-weight:600;font-size:0.8125rem;
                                          padding:0.5rem 1rem;border-radius:0.625rem;cursor:pointer;
                                          transition:background 0.15s;"
                                   onmouseover="this.style.background='#1d4ed8'"
                                   onmouseout="this.style.background='#2563eb'">
                                <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                {{ $parishioner?->photo_path ? 'Change Photo' : 'Upload Photo' }}
                            </label>

                            {{-- Remove button --}}
                            @if($parishioner?->photo_path)
                            <button type="button"
                                    onclick="if(confirm('Remove your profile photo?')){document.getElementById('remove-photo-form').submit();}"
                                    style="display:inline-flex;align-items:center;gap:6px;
                                           background:#fff;color:#dc2626;font-weight:600;font-size:0.8125rem;
                                           padding:0.5rem 1rem;border-radius:0.625rem;cursor:pointer;
                                           border:1.5px solid #fecaca;transition:all 0.15s;"
                                    onmouseover="this.style.background='#fef2f2'"
                                    onmouseout="this.style.background='#fff'">
                                <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Remove
                            </button>
                            @endif
                        </div>

                        {{-- Cropped badge --}}
                        <p id="photo-cropped-badge"
                           style="display:none;font-size:0.75rem;background:#f0fdf4;
                                  border:1px solid #bbf7d0;border-radius:0.5rem;
                                  padding:4px 10px;margin-bottom:4px;
                                  align-items:center;gap:5px;">
                            <svg style="width:12px;height:12px;color:#16a34a;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span id="photo-cropped-name">Photo cropped and ready</span>
                        </p>
                        <p style="font-size:0.72rem;color:#94a3b8;">JPG, PNG or WebP · max 5 MB · will be cropped to a square</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Personal Information ── --}}
        <div class="ps">
            <div class="ps-head">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <h2>Personal Information</h2>
            </div>
            <div class="ps-body space-y-4">

                <div class="field-row">
                    <div class="fl">
                        <label>First Name <span>*</span></label>
                        <input type="text" name="first_name"
                               value="{{ old('first_name', $parishioner?->first_name) }}"
                               required placeholder="Juan">
                        @error('first_name')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="fl">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name"
                               value="{{ old('middle_name', $parishioner?->middle_name) }}"
                               placeholder="Santos">
                    </div>
                </div>

                <div class="field-row">
                    <div class="fl">
                        <label>Last Name <span>*</span></label>
                        <input type="text" name="last_name"
                               value="{{ old('last_name', $parishioner?->last_name) }}"
                               required placeholder="Dela Cruz">
                        @error('last_name')<p class="err">{{ $message }}</p>@enderror
                    </div>
                    <div class="fl">
                        <label>Suffix</label>
                        <input type="text" name="suffix"
                               value="{{ old('suffix', $parishioner?->suffix) }}"
                               placeholder="Jr., Sr., III">
                    </div>
                </div>

                <div class="field-row">
                    <div class="fl">
                        <label>Birthdate</label>
                        <input type="date" name="birthdate"
                               value="{{ old('birthdate', $parishioner?->birthdate?->format('Y-m-d')) }}">
                    </div>
                    <div class="fl">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">Select...</option>
                            <option value="male"   @selected(old('gender', $parishioner?->gender) === 'male')>Male</option>
                            <option value="female" @selected(old('gender', $parishioner?->gender) === 'female')>Female</option>
                        </select>
                    </div>
                </div>

                <div class="field-row">
                    <div class="fl">
                        <label>Civil Status</label>
                        <select name="civil_status">
                            <option value="">Select...</option>
                            @foreach(['single'=>'Single','married'=>'Married','widowed'=>'Widowed','separated'=>'Separated','annulled'=>'Annulled'] as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('civil_status', $parishioner?->civil_status) === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fl">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number"
                               value="{{ old('contact_number', $parishioner?->contact_number) }}"
                               placeholder="09XX-XXX-XXXX">
                    </div>
                </div>

                <div class="field-row full">
                    <div class="fl">
                        <label>Email Address</label>
                        <input type="email" value="{{ auth()->user()->email }}" disabled>
                        <p class="hint">Email cannot be changed. Contact the parish office if needed.</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Address ── --}}
        <div class="ps">
            <div class="ps-head">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <h2>Address</h2>
            </div>
            <div class="ps-body space-y-4">

                <div class="field-row full">
                    <div class="fl">
                        <label>Street Address</label>
                        <input type="text" name="address"
                               value="{{ old('address', $parishioner?->address) }}"
                               placeholder="Block, Lot, Street">
                    </div>
                </div>

                <div class="field-row">
                    <div class="fl">
                        <label>Barangay</label>
                        <input type="text" name="barangay"
                               value="{{ old('barangay', $parishioner?->barangay ?? 'Niugan') }}">
                    </div>
                    <div class="fl">
                        <label>City / Municipality</label>
                        <input type="text" name="city"
                               value="{{ old('city', $parishioner?->city ?? 'Cabuyao') }}">
                    </div>
                </div>

                <div class="field-row">
                    <div class="fl">
                        <label>Province</label>
                        <input type="text" name="province"
                               value="{{ old('province', $parishioner?->province ?? 'Laguna') }}">
                    </div>
                    <div class="fl">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code"
                               value="{{ old('postal_code', $parishioner?->postal_code ?? '4025') }}">
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Family Info (read-only) ── --}}
        @if($parishioner?->family)
        <div class="ps">
            <div class="ps-head">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <h2>Family Information</h2>
            </div>
            <div class="ps-body">
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.75rem;padding:1rem;">
                    <p style="font-weight:700;font-size:0.9rem;color:#1e3a8a;">{{ $parishioner->family->family_name }}</p>
                    <p style="font-size:0.8rem;color:#3b82f6;margin-top:2px;">{{ $parishioner->family->members_count ?? 0 }} family members</p>
                    @if($parishioner->is_head_of_family)
                    <span style="display:inline-flex;align-items:center;gap:4px;margin-top:8px;background:#2563eb;color:#fff;font-size:0.7rem;font-weight:700;padding:2px 10px;border-radius:9999px;">
                        ★ Head of Family
                    </span>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ── Save Button ── --}}
        <div style="display:flex;gap:0.75rem;align-items:center;">
            <button type="submit"
                    style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;font-weight:700;font-size:0.9rem;padding:0.75rem 2rem;border-radius:0.75rem;border:none;cursor:pointer;box-shadow:0 4px 14px rgba(37,99,235,0.35);transition:all 0.2s;"
                    onmouseover="this.style.background='#1d4ed8';this.style.transform='translateY(-1px)';"
                    onmouseout="this.style.background='#2563eb';this.style.transform='';">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Save Changes
            </button>
            <a href="{{ route('parishioner.dashboard') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#374151;font-weight:600;font-size:0.9rem;padding:0.75rem 1.5rem;border-radius:0.75rem;border:1.5px solid #e2e8f0;text-decoration:none;transition:all 0.2s;"
               onmouseover="this.style.background='#f8fafc';"
               onmouseout="this.style.background='#fff';">
                Cancel
            </a>
        </div>
    </form>

    {{-- ── Sacramental Records (read-only) ── --}}
    @if($parishioner && $parishioner->sacramentalRecords()->count() > 0)
    <div class="ps">
        <div class="ps-head">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h2>My Sacramental Records</h2>
        </div>
        <div class="ps-body">
            <div class="space-y-2">
                @foreach($parishioner->sacramentalRecords as $record)
                @php
                    $tl = ['baptism'=>'Baptism','first_communion'=>'First Communion','confirmation'=>'Confirmation','marriage'=>'Marriage','death_burial'=>'Death/Burial'];
                    $tc = ['baptism'=>'#2563eb','first_communion'=>'#16a34a','confirmation'=>'#7c3aed','marriage'=>'#db2777','death_burial'=>'#6b7280'];
                    $bg = ['baptism'=>'#dbeafe','first_communion'=>'#dcfce7','confirmation'=>'#ede9fe','marriage'=>'#fce7f3','death_burial'=>'#f3f4f6'];
                @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.75rem 1rem;background:#f8fafc;border-radius:0.75rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <div style="width:36px;height:36px;border-radius:8px;background:{{ $bg[$record->type] ?? '#f3f4f6' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:18px;height:18px;color:{{ $tc[$record->type] ?? '#6b7280' }};" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p style="font-weight:600;font-size:0.875rem;color:#0f172a;">{{ $tl[$record->type] ?? $record->type }}</p>
                            <p style="font-size:0.75rem;color:#64748b;">{{ $record->date_administered->format('F d, Y') }}</p>
                        </div>
                    </div>
                    @if($record->verified_at)
                    <span style="font-size:0.72rem;font-weight:700;color:#16a34a;">✓ Verified</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>{{-- /.profile-wrap --}}

{{-- ── Remove Photo form (outside main form to avoid nesting) ── --}}
@if($parishioner?->photo_path)
<form id="remove-photo-form" method="POST"
      action="{{ route('parishioner.profile.remove-photo') }}"
      style="display:none;">
    @csrf @method('DELETE')
</form>
@endif

{{-- ══════════════════════════════════════════════════════
     CROP MODAL (Cropper.js)
     ══════════════════════════════════════════════════════ --}}
<div id="crop-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="crop-modal-title">
    <div id="crop-modal">

        {{-- Header --}}
        <div class="crop-modal-header">
            <h3 id="crop-modal-title">
                <svg style="width:18px;height:18px;color:#2563eb;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Crop Profile Photo
            </h3>
            <button type="button" onclick="closeCropModal()"
                    style="width:30px;height:30px;border-radius:50%;border:none;background:#f1f5f9;
                           display:flex;align-items:center;justify-content:center;cursor:pointer;color:#64748b;">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Cropper canvas area --}}
        <div class="crop-modal-body">
            <img id="crop-image" src="" alt="Crop">
        </div>

        {{-- Toolbar --}}
        <div class="crop-modal-toolbar">
            <p style="font-size:0.72rem;color:#64748b;flex:1;min-width:140px;">
                Drag to move &nbsp;·&nbsp; Scroll to zoom &nbsp;·&nbsp; Square crop
            </p>
            <div class="crop-tool-group">
                {{-- Zoom in --}}
                <button type="button" class="crop-tool-btn" onclick="cropperInstance.zoom(0.1)" title="Zoom in">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0zM11 8v6M8 11h6"/></svg>
                </button>
                {{-- Zoom out --}}
                <button type="button" class="crop-tool-btn" onclick="cropperInstance.zoom(-0.1)" title="Zoom out">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0zM8 11h6"/></svg>
                </button>
                {{-- Rotate left --}}
                <button type="button" class="crop-tool-btn" onclick="cropperInstance.rotate(-90)" title="Rotate left">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c2.474 0 4.733.9 6.465 2.387M15 3l-3 3 3 3"/></svg>
                </button>
                {{-- Rotate right --}}
                <button type="button" class="crop-tool-btn" onclick="cropperInstance.rotate(90)" title="Rotate right">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.542 12C20.268 7.943 16.477 5 12 5c-2.474 0-4.733.9-6.465 2.387M9 3l3 3-3 3"/></svg>
                </button>
                {{-- Flip horizontal --}}
                <button type="button" class="crop-tool-btn" onclick="cropperInstance.scaleX(-cropperInstance.getData().scaleX||1)" title="Flip horizontal">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3H5a2 2 0 00-2 2v14a2 2 0 002 2h3m8-18h3a2 2 0 012 2v14a2 2 0 01-2 2h-3M12 3v18"/></svg>
                </button>
                {{-- Reset --}}
                <button type="button" class="crop-tool-btn" onclick="cropperInstance.reset()" title="Reset">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="crop-modal-footer">
            {{-- Live circle preview --}}
            <div id="crop-preview-circle"></div>
            <button type="button" onclick="closeCropModal()"
                    style="padding:0.5rem 1.125rem;border-radius:0.625rem;border:1.5px solid #e2e8f0;
                           background:#fff;color:#374151;font-weight:600;font-size:0.875rem;cursor:pointer;">
                Cancel
            </button>
            <button type="button" id="crop-confirm-btn" onclick="confirmCrop()"
                    style="padding:0.5rem 1.375rem;border-radius:0.625rem;border:none;
                           background:#2563eb;color:#fff;font-weight:700;font-size:0.875rem;
                           cursor:pointer;display:inline-flex;align-items:center;gap:6px;
                           transition:background 0.15s;"
                    onmouseover="this.style.background='#1d4ed8'"
                    onmouseout="this.style.background='#2563eb'">
                <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Use This Photo
            </button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
{{-- Cropper.js (CDN) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script>
// ── Cropper.js state ─────────────────────────────────────────────────────────
let cropperInstance = null;
let currentFileName = 'photo.jpg';

// ── Avatar hover overlay ─────────────────────────────────────────────────────
const previewEl = document.getElementById('photo-preview');
const overlayEl = document.getElementById('photo-overlay');
if (previewEl && overlayEl) {
    previewEl.addEventListener('mouseenter', () => overlayEl.style.opacity = '1');
    previewEl.addEventListener('mouseleave', () => overlayEl.style.opacity = '0');
}

// ── 1. File selected → open crop modal ───────────────────────────────────────
function openCropModal(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    // Client-side size guard (5 MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('File is too large. Please choose an image under 5 MB.');
        input.value = '';
        return;
    }

    currentFileName = file.name;
    const reader = new FileReader();
    reader.onload = function (e) {
        const cropImg = document.getElementById('crop-image');
        cropImg.src = e.target.result;

        // Show modal
        const backdrop = document.getElementById('crop-modal-backdrop');
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';

        // Destroy previous cropper if any
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }

        // Init Cropper.js — square aspect ratio (1:1), live preview circle
        cropperInstance = new Cropper(cropImg, {
            aspectRatio: 1,
            viewMode: 1,            // restrict crop box to canvas
            dragMode: 'move',       // move image, not crop box
            autoCropArea: 0.85,     // 85% of canvas filled by default
            responsive: true,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            preview: '#crop-preview-circle',
        });
    };
    reader.readAsDataURL(file);

    // Reset the file input so the same file can be re-selected
    input.value = '';
}

// ── 2. Cancel → close modal without saving ───────────────────────────────────
function closeCropModal() {
    const backdrop = document.getElementById('crop-modal-backdrop');
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
    if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
}

// ── 3. Confirm → export cropped canvas as WebP, store in hidden input ────────
function confirmCrop() {
    if (!cropperInstance) return;

    const btn = document.getElementById('crop-confirm-btn');
    btn.disabled = true;
    btn.innerHTML = '<svg style="width:14px;height:14px;animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24">'
        + '<circle style="opacity:.3" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>'
        + '<path style="opacity:.8" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/>'
        + '</svg> Processing…';

    // Give the UI a tick to render the spinner, then do the heavy canvas work
    requestAnimationFrame(() => {
        setTimeout(() => {
            try {
                // Export at 400×400 px — plenty for profile photos, keeps file small
                const canvas = cropperInstance.getCroppedCanvas({ width: 400, height: 400 });

                canvas.toBlob(function (blob) {
                    // Convert blob → base64 to pass through a hidden form field
                    const fr = new FileReader();
                    fr.onload = function () {
                        // Store base64 in the hidden input
                        document.getElementById('photo-cropped-data').value = fr.result;

                        // Update the avatar preview circle
                        const previewDiv = document.getElementById('photo-preview');
                        const placeholder = document.getElementById('photo-placeholder');
                        if (placeholder) placeholder.style.display = 'none';

                        let img = document.getElementById('photo-img');
                        if (!img) {
                            img = document.createElement('img');
                            img.id = 'photo-img';
                            img.style.cssText = 'width:100%;height:100%;object-fit:cover;transition:opacity 0.25s;';
                            previewDiv.insertBefore(img, overlayEl);
                        }
                        img.style.opacity = '0';
                        img.src = fr.result;
                        img.onload = () => { img.style.opacity = '1'; };

                        // Show "cropped and ready" badge
                        const badge = document.getElementById('photo-cropped-badge');
                        const nameEl = document.getElementById('photo-cropped-name');
                        if (badge) { badge.style.display = 'inline-flex'; }
                        if (nameEl) { nameEl.textContent = 'Photo cropped and ready to save'; }

                        closeCropModal();
                    };
                    fr.readAsDataURL(blob);
                }, 'image/webp', 0.92);  // WebP, 92% quality

            } catch (err) {
                alert('Could not process the image. Please try a different file.');
                btn.disabled = false;
                btn.innerHTML = '<svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Use This Photo';
            }
        }, 50);
    });
}

// ── Close modal on backdrop click ────────────────────────────────────────────
document.getElementById('crop-modal-backdrop').addEventListener('click', function (e) {
    if (e.target === this) closeCropModal();
});

// ── Close modal on Escape key ────────────────────────────────────────────────
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeCropModal();
});
</script>
@endpush
