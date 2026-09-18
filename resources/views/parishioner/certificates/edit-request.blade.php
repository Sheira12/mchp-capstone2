@extends('layouts.portal')
@section('title', 'Request Correction')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
        <a href="{{ route('parishioner.certificates.index') }}"
           style="width:38px;height:38px;background:#fff;border:1.5px solid #e2e8f0;border-radius:0.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none;transition:background 0.15s;"
           onmouseover="this.style.background='#f8faff';" onmouseout="this.style.background='#fff';">
            <svg style="width:16px;height:16px;color:#475569;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 style="font-size:1.375rem;font-weight:800;color:#0f172a;margin:0 0 3px;">Request Correction</h1>
            <p style="font-size:0.875rem;color:#64748b;margin:0;">
                {{ $certificate->getTypeLabel() }} · {{ $certificate->certificate_number }}
            </p>
        </div>
    </div>

    {{-- Info banner --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:1rem;padding:1rem 1.25rem;font-size:0.875rem;color:#1e40af;">
        <div style="display:flex;align-items:flex-start;gap:10px;">
            <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p style="font-weight:700;margin:0 0 2px;">How corrections work</p>
                <p style="margin:0;opacity:0.85;line-height:1.6;">
                    Your correction request will be reviewed by parish staff. Only approved corrections
                    will be reflected on the generated certificate. You will be notified once reviewed.
                </p>
            </div>
        </div>
    </div>

    {{-- Pending request notice --}}
    @if($pendingRequest)
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:1rem;padding:1rem 1.25rem;">
        <div style="display:flex;align-items:flex-start;gap:10px;font-size:0.875rem;color:#92400e;">
            <svg style="width:18px;height:18px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p style="font-weight:700;margin:0 0 2px;">Awaiting Staff Approval</p>
                <p style="margin:0;opacity:0.9;">
                    You have a correction request submitted {{ $pendingRequest->created_at->diffForHumans() }} that is currently awaiting review.
                    You cannot submit another request until this one is resolved.
                </p>
                <form method="POST" action="{{ route('parishioner.certificates.edit-request.cancel', $pendingRequest) }}" class="mt-2" onsubmit="return confirm('Cancel this pending correction request?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="font-size:0.8rem;font-weight:600;color:#dc2626;background:none;border:none;padding:0;cursor:pointer;text-decoration:underline;">
                        Cancel this request
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Current values from registry --}}
    <div style="background:#fff;border-radius:1.25rem;border:1px solid #e8edf5;padding:1.5rem;">
        <h2 style="font-size:0.875rem;font-weight:700;color:#374151;margin:0 0 1rem;text-transform:uppercase;letter-spacing:0.06em;">Current Parish Registry Data</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0.875rem;">
            @php $rec = $certificate->sacramentalRecord; @endphp
            @if($rec)
            <div><span style="font-size:0.7rem;color:#94a3b8;text-transform:uppercase;">Date</span><p style="margin:2px 0 0;font-size:0.875rem;font-weight:600;color:#0f172a;">{{ $rec->date_administered?->format('M d, Y') ?? '—' }}</p></div>
            <div><span style="font-size:0.7rem;color:#94a3b8;text-transform:uppercase;">Officiating Priest</span><p style="margin:2px 0 0;font-size:0.875rem;font-weight:600;color:#0f172a;">{{ $rec->celebrant ?? '—' }}</p></div>
            <div><span style="font-size:0.7rem;color:#94a3b8;text-transform:uppercase;">Venue</span><p style="margin:2px 0 0;font-size:0.875rem;font-weight:600;color:#0f172a;">{{ $rec->venue ?? '—' }}</p></div>
            @if(!empty($rec->godparents))
            <div><span style="font-size:0.7rem;color:#94a3b8;text-transform:uppercase;">Godparents / Sponsors</span><p style="margin:2px 0 0;font-size:0.875rem;font-weight:600;color:#0f172a;">{{ implode(', ', (array)$rec->godparents) }}</p></div>
            @endif
            @if(!empty($rec->sponsors))
            <div><span style="font-size:0.7rem;color:#94a3b8;text-transform:uppercase;">Sponsors</span><p style="margin:2px 0 0;font-size:0.875rem;font-weight:600;color:#0f172a;">{{ implode(', ', (array)$rec->sponsors) }}</p></div>
            @endif
            @if(!empty($rec->witnesses))
            <div><span style="font-size:0.7rem;color:#94a3b8;text-transform:uppercase;">Witnesses</span><p style="margin:2px 0 0;font-size:0.875rem;font-weight:600;color:#0f172a;">{{ implode(', ', (array)$rec->witnesses) }}</p></div>
            @endif
            @else
            <p style="color:#94a3b8;font-size:0.875rem;">No linked sacramental record on file.</p>
            @endif
        </div>
    </div>

    {{-- Correction form --}}
    @if(!$pendingRequest)
    <div style="background:#fff;border-radius:1.25rem;border:1px solid #e8edf5;padding:1.5rem;">
        <h2 style="font-size:0.875rem;font-weight:700;color:#374151;margin:0 0 1.25rem;text-transform:uppercase;letter-spacing:0.06em;">Submit Correction</h2>

        <form method="POST" action="{{ route('parishioner.certificates.edit-request.store', $certificate) }}" class="space-y-4">
            @csrf

            @if($errors->any())
            <div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:0.75rem;padding:0.875rem 1rem;font-size:0.875rem;color:#991b1b;">
                <ul style="margin:0;padding-left:1.25rem;">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1rem;">
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Date Administered</label>
                    <input type="date" name="date_administered" value="{{ old('date_administered') }}"
                           style="width:100%;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;">
                </div>
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Officiating Priest</label>
                    <input type="text" name="celebrant" value="{{ old('celebrant') }}" placeholder="Rev. Fr. …"
                           style="width:100%;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;">
                </div>
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Venue / Church</label>
                    <input type="text" name="venue" value="{{ old('venue') }}" placeholder="Church name…"
                           style="width:100%;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;">
                </div>
                @if(in_array($certificate->type, ['marriage']))
                <div>
                    <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Spouse Name</label>
                    <input type="text" name="spouse_name" value="{{ old('spouse_name') }}" placeholder="Full name of spouse"
                           style="width:100%;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;">
                </div>
                @endif
            </div>

            {{-- Ninong/Ninang for baptism and marriage --}}
            @if(in_array($certificate->type, ['baptism', 'marriage', 'confirmation']))
            <div id="ninong-wrap">
                <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                    {{ $certificate->type === 'confirmation' ? 'Sponsor(s)' : 'Godfathers / Ninong' }}
                    <button type="button" onclick="addRow('ninong-list','ninong[]')"
                            style="margin-left:8px;font-size:0.75rem;color:#2563eb;background:none;border:none;cursor:pointer;font-weight:700;">+ Add</button>
                </label>
                <div id="ninong-list" class="space-y-2">
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="ninong[]" value="{{ old('ninong.0') }}" placeholder="Name"
                               style="flex:1;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;">
                    </div>
                </div>
            </div>
            @endif

            @if(in_array($certificate->type, ['baptism', 'marriage']))
            <div id="ninang-wrap">
                <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                    Godmothers / Ninang
                    <button type="button" onclick="addRow('ninang-list','ninang[]')"
                            style="margin-left:8px;font-size:0.75rem;color:#2563eb;background:none;border:none;cursor:pointer;font-weight:700;">+ Add</button>
                </label>
                <div id="ninang-list" class="space-y-2">
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="ninang[]" value="{{ old('ninang.0') }}" placeholder="Name"
                               style="flex:1;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;">
                    </div>
                </div>
            </div>
            @endif

            @if(in_array($certificate->type, ['marriage']))
            <div>
                <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
                    Witnesses
                    <button type="button" onclick="addRow('witness-list','witnesses[]')"
                            style="margin-left:8px;font-size:0.75rem;color:#2563eb;background:none;border:none;cursor:pointer;font-weight:700;">+ Add</button>
                </label>
                <div id="witness-list" class="space-y-2">
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="witnesses[]" value="{{ old('witnesses.0') }}" placeholder="Witness 1"
                               style="flex:1;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;">
                    </div>
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="witnesses[]" value="{{ old('witnesses.1') }}" placeholder="Witness 2"
                               style="flex:1;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;">
                    </div>
                </div>
            </div>
            @endif

            <div>
                <label style="font-size:0.78rem;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Note to Parish Staff <span style="color:#94a3b8;font-weight:400;">(optional)</span></label>
                <textarea name="parishioner_note" rows="3" placeholder="Explain why the correction is needed…"
                          style="width:100%;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;resize:vertical;">{{ old('parishioner_note') }}</textarea>
            </div>

            <div style="display:flex;gap:0.75rem;padding-top:0.5rem;">
                <button type="submit"
                        style="display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#fff;font-weight:700;font-size:0.875rem;padding:0.625rem 1.5rem;border-radius:0.75rem;border:none;cursor:pointer;transition:background 0.15s;"
                        onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                    <svg style="width:15px;height:15px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Submit Correction
                </button>
                <a href="{{ route('parishioner.certificates.index') }}"
                   style="display:inline-flex;align-items:center;padding:0.625rem 1.25rem;background:#f1f5f9;color:#475569;font-weight:600;font-size:0.875rem;border-radius:0.75rem;text-decoration:none;transition:background 0.15s;"
                   onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function addRow(containerId, inputName) {
    const container = document.getElementById(containerId);
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;gap:8px;margin-top:6px;';
    div.innerHTML = `<input type="text" name="${inputName}" placeholder="Name"
        style="flex:1;padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;">
        <button type="button" onclick="this.parentElement.remove()"
            style="width:32px;height:36px;background:#fee2e2;color:#dc2626;border:none;border-radius:0.5rem;cursor:pointer;font-size:1rem;flex-shrink:0;">×</button>`;
    container.appendChild(div);
}
</script>
@endpush
