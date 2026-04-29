@extends('layouts.portal')

@section('title', 'My Profile')

@push('styles')
<style>
.profile-wrap {
    max-width: 680px;
    margin: 0 auto;
}
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
.fl .err { font-size: 0.72rem; color: #ef4444; margin-top: 2px; }
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
                <div style="display:flex;align-items:center;gap:1.25rem;">
                    <div id="photo-preview" style="width:80px;height:80px;border-radius:50%;overflow:hidden;background:linear-gradient(135deg,#dbeafe,#e0e7ff);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                        @if($parishioner?->photo_path)
                            <img src="{{ Storage::url($parishioner->photo_path) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <svg style="width:32px;height:32px;color:#93c5fd;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @endif
                    </div>
                    <div>
                        <input type="file" name="photo" id="photo-input" accept="image/*" class="hidden" onchange="previewPhoto(this)">
                        <label for="photo-input" style="display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#fff;font-weight:600;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;cursor:pointer;transition:background 0.15s;"
                               onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Upload Photo
                        </label>
                        <p style="font-size:0.72rem;color:#94a3b8;margin-top:6px;">JPG or PNG, max 2MB</p>
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

</div>
@endsection

@push('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('photo-preview').innerHTML =
                `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
