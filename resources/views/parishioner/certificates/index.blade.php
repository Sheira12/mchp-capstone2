@extends('layouts.portal')

@section('title', 'My Certificates')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;color:#0f172a;margin:0 0 4px;">My Certificates</h1>
            <p style="font-size:0.875rem;color:#64748b;margin:0;">View, download, and verify your official parish certificates</p>
        </div>
        <a href="{{ route('parishioner.certificates.create') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;font-weight:700;font-size:0.875rem;padding:0.75rem 1.5rem;border-radius:0.875rem;text-decoration:none;box-shadow:0 4px 14px rgba(37,99,235,0.3);transition:all 0.2s;white-space:nowrap;"
           onmouseover="this.style.background='#1d4ed8';this.style.transform='translateY(-1px)';"
           onmouseout="this.style.background='#2563eb';this.style.transform='';">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Request Certificate
        </a>
    </div>

    {{-- Search & Filter bar --}}
    <form id="cert-search-form" method="GET" action="{{ route('parishioner.certificates.index') }}"
          style="background:#fff;border-radius:1rem;border:1px solid #e8edf5;box-shadow:0 2px 8px rgba(0,0,0,0.04);padding:1rem;display:flex;flex-wrap:wrap;gap:0.75rem;align-items:flex-end;">
        {{-- Search --}}
        <div style="flex:1;min-width:200px;">
            <label style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">
                Search
                <svg id="cert-spinner" style="display:none;width:11px;height:11px;margin-left:4px;vertical-align:middle;animation:spin 0.7s linear infinite;" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="#2563eb" stroke-width="4" style="opacity:0.25"/>
                    <path fill="#2563eb" d="M4 12a8 8 0 018-8v8z" style="opacity:0.75"/>
                </svg>
            </label>
            <div style="position:relative;">
                <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#94a3b8;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7 7 0 105.65 5.65a7 7 0 0011.35 11.35z"/>
                </svg>
                <input type="text" id="cert-search-input" name="search" value="{{ request('search') }}"
                       placeholder="Certificate #, purpose…"
                       autocomplete="off"
                       style="width:100%;padding:0.5rem 0.75rem 0.5rem 2rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;color:#0f172a;outline:none;transition:border-color 0.15s;"
                       onfocus="this.style.borderColor='#2563eb';" onblur="this.style.borderColor='#e2e8f0';">
            </div>
        </div>
        {{-- Type filter --}}
        <div>
            <label style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">Type</label>
            <select name="type" onchange="document.getElementById('cert-search-form').submit()"
                    style="padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;color:#0f172a;background:#fff;cursor:pointer;">
                <option value="">All Types</option>
                <option value="baptism"         @selected(request('type')==='baptism')>Baptism</option>
                <option value="confirmation"    @selected(request('type')==='confirmation')>Confirmation</option>
                <option value="marriage"        @selected(request('type')==='marriage')>Marriage</option>
                <option value="first_communion" @selected(request('type')==='first_communion')>First Communion</option>
                <option value="death_burial"    @selected(request('type')==='death_burial')>Death/Burial</option>
                <option value="no_impediment"   @selected(request('type')==='no_impediment')>No Impediment</option>
                <option value="membership"      @selected(request('type')==='membership')>Membership</option>
            </select>
        </div>
        {{-- Status filter --}}
        <div>
            <label style="font-size:0.72rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:4px;">Status</label>
            <select name="status" onchange="document.getElementById('cert-search-form').submit()"
                    style="padding:0.5rem 0.75rem;border:1.5px solid #e2e8f0;border-radius:0.625rem;font-size:0.875rem;color:#0f172a;background:#fff;cursor:pointer;">
                <option value="">All Status</option>
                <option value="draft"    @selected(request('status')==='draft')>Processing</option>
                <option value="issued"   @selected(request('status')==='issued')>Issued</option>
                <option value="released" @selected(request('status')==='released')>Released</option>
            </select>
        </div>
        @if(request()->hasAny(['search','type','status']))
        <a href="{{ route('parishioner.certificates.index') }}"
           style="display:inline-flex;align-items:center;gap:5px;padding:0.5rem 1rem;background:#f1f5f9;color:#475569;font-size:0.8125rem;font-weight:600;border-radius:0.625rem;text-decoration:none;align-self:flex-end;transition:background 0.15s;"
           onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
            <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Clear
        </a>
        @endif
    </form>

    @if($certificates->isEmpty())
    @if(request()->hasAny(['search','type','status']))
    {{-- Empty search results --}}
    <div style="background:#fff;border-radius:1.25rem;border:1px solid #f1f5f9;box-shadow:0 2px 8px rgba(0,0,0,0.04);padding:4rem 2rem;text-align:center;">
        <div style="width:72px;height:72px;background:#eff6ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
            <svg style="width:36px;height:36px;color:#2563eb;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m0 0A7 7 0 105.65 5.65a7 7 0 0011.35 11.35z"/>
            </svg>
        </div>
        <h3 style="font-size:1.125rem;font-weight:700;color:#0f172a;margin:0 0 0.5rem;">No results found</h3>
        <p style="font-size:0.875rem;color:#64748b;max-width:360px;margin:0 auto 1.5rem;line-height:1.6;">
            No certificates matched your search. Try different keywords or clear the filters.
        </p>
        <a href="{{ route('parishioner.certificates.index') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;font-weight:700;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:0.875rem;text-decoration:none;">
            Clear Search
        </a>
    </div>
    @else
    {{-- No certificates at all --}}
    <div style="background:#fff;border-radius:1.25rem;border:1px solid #f1f5f9;box-shadow:0 2px 8px rgba(0,0,0,0.04);padding:4rem 2rem;text-align:center;">
        <div style="width:72px;height:72px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
            <svg style="width:36px;height:36px;color:#d97706;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 style="font-size:1.125rem;font-weight:700;color:#0f172a;margin:0 0 0.5rem;">No certificates yet</h3>
        <p style="font-size:0.875rem;color:#64748b;max-width:360px;margin:0 auto 1.5rem;line-height:1.6;">
            Certificates will appear here once issued by the parish office. Request one online or visit the parish office.
        </p>
        <a href="{{ route('parishioner.certificates.create') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;font-weight:700;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:0.875rem;text-decoration:none;box-shadow:0 4px 14px rgba(37,99,235,0.3);">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Request a Certificate
        </a>
    </div>
    @endif

    @else

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(min(320px,100%),1fr));gap:1.25rem;">
        @foreach($certificates as $cert)
        @php
            $statusMap = [
                'draft'    => ['bg'=>'#f1f5f9','color'=>'#475569','label'=>'Processing'],
                'issued'   => ['bg'=>'#dbeafe','color'=>'#1d4ed8','label'=>'Issued'],
                'released' => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'Released'],
            ];
            $sm = $statusMap[$cert->status] ?? $statusMap['draft'];

            $typeMap = [
                'baptism'         => ['icon'=>'💧','bg'=>'#eff6ff','color'=>'#2563eb'],
                'confirmation'    => ['icon'=>'✝️','bg'=>'#f5f3ff','color'=>'#7c3aed'],
                'marriage'        => ['icon'=>'💍','bg'=>'#fdf2f8','color'=>'#db2777'],
                'first_communion' => ['icon'=>'🕊️','bg'=>'#f0fdf4','color'=>'#16a34a'],
                'death_burial'    => ['icon'=>'🕯️','bg'=>'#f8fafc','color'=>'#475569'],
                'no_impediment'   => ['icon'=>'📋','bg'=>'#fffbeb','color'=>'#d97706'],
                'membership'      => ['icon'=>'🏛️','bg'=>'#eff6ff','color'=>'#2563eb'],
            ];
            $tm = $typeMap[$cert->type] ?? ['icon'=>'📜','bg'=>'#f8faff','color'=>'#2563eb'];

            // Use model method — gates on both status AND record_verification_status
            $canDownload = $cert->isDownloadable();
            $verStatus   = $cert->record_verification_status ?? 'pending';
        @endphp

        <div style="background:#fff;border-radius:1.25rem;border:1px solid #e8edf5;box-shadow:0 2px 8px rgba(0,0,0,0.05);overflow:hidden;transition:all 0.25s ease;"
             onmouseover="this.style.boxShadow='0 8px 24px rgba(37,99,235,0.12)';this.style.transform='translateY(-3px)';"
             onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.05)';this.style.transform='';">

            {{-- Status bar --}}
            <div style="height:4px;background:{{ $sm['color'] }};opacity:0.7;"></div>

            <div style="padding:1.5rem;">
                {{-- Header row --}}
                <div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1rem;">
                    <div style="width:52px;height:52px;border-radius:12px;background:{{ $tm['bg'] }};display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">
                        {{ $tm['icon'] }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <h3 style="font-size:0.9375rem;font-weight:700;color:#0f172a;margin:0 0 3px;text-transform:capitalize;">
                            {{ str_replace('_', ' ', $cert->type) }} Certificate
                        </h3>
                        <p style="font-size:0.72rem;color:#94a3b8;font-family:monospace;margin:0;">{{ $cert->certificate_number }}</p>
                        <p style="font-size:0.78rem;color:#64748b;margin:3px 0 0;">
                            Issued {{ $cert->issued_date->format('M d, Y') }}
                        </p>
                    </div>
                    <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:9999px;font-size:0.7rem;font-weight:700;background:{{ $sm['bg'] }};color:{{ $sm['color'] }};flex-shrink:0;">
                        {{ $sm['label'] }}
                    </span>
                </div>

                {{-- Purpose --}}
                @if($cert->purpose)
                <div style="background:#f8faff;border-radius:0.625rem;padding:0.625rem 0.875rem;margin-bottom:1rem;font-size:0.8rem;color:#475569;">
                    <span style="font-weight:600;color:#374151;">Purpose:</span> {{ $cert->purpose }}
                </div>
                @endif

                {{-- Sacramental record link --}}
                @if($cert->sacramentalRecord)
                <div style="background:#f0fdf4;border-radius:0.625rem;padding:0.625rem 0.875rem;margin-bottom:1rem;font-size:0.78rem;color:#166534;display:flex;align-items:center;gap:6px;">
                    <svg style="width:14px;height:14px;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Linked to {{ ucfirst(str_replace('_',' ',$cert->sacramentalRecord->type)) }} record
                    · {{ $cert->sacramentalRecord->date_administered->format('M d, Y') }}
                </div>
                @endif

                {{-- Record Verification Status badge --}}
                @if(in_array($cert->type, \App\Models\Certificate::REQUIRES_RECORD))
                @php
                    $vBg    = match($verStatus) {
                        'verified'   => '#d1fae5',
                        'unverified' => '#fee2e2',
                        default      => '#fef3c7',
                    };
                    $vColor = match($verStatus) {
                        'verified'   => '#065f46',
                        'unverified' => '#991b1b',
                        default      => '#92400e',
                    };
                    $vLabel = match($verStatus) {
                        'verified'   => '✓ Record Verified',
                        'unverified' => '⚠ No Record Found',
                        default      => '⏳ Pending Verification',
                    };
                    $vIcon = match($verStatus) {
                        'verified'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                        'unverified' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                        default      => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    };
                @endphp
                <div style="background:{{ $vBg }};border-radius:0.625rem;padding:0.625rem 0.875rem;margin-bottom:1rem;font-size:0.8rem;color:{{ $vColor }};display:flex;align-items:flex-start;gap:6px;">
                    <svg style="width:14px;height:14px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $vIcon }}"/></svg>
                    <div>
                        <span style="font-weight:700;">{{ $vLabel }}</span>
                        @if($cert->staff_notes)
                        <br><span style="font-size:0.75rem;opacity:0.8;">{{ $cert->staff_notes }}</span>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Actions --}}
                <div style="display:flex;align-items:center;gap:0.75rem;padding-top:1rem;border-top:1px solid #f1f5f9;flex-wrap:wrap;">

                    @if($canDownload)
                    {{-- Download PDF --}}
                    <a href="{{ route('parishioner.certificates.download', $cert) }}"
                       style="display:inline-flex;align-items:center;gap:6px;background:#2563eb;color:#fff;font-weight:700;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;text-decoration:none;transition:all 0.2s;box-shadow:0 2px 8px rgba(37,99,235,0.25);"
                       onmouseover="this.style.background='#1d4ed8';this.style.transform='translateY(-1px)';"
                       onmouseout="this.style.background='#2563eb';this.style.transform='';">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download PDF
                    </a>
                    @elseif($verStatus === 'unverified')
                    {{-- Blocked: no record found --}}
                    <span style="display:inline-flex;align-items:center;gap:6px;background:#fee2e2;color:#991b1b;font-size:0.8125rem;font-weight:600;padding:0.5rem 1.125rem;border-radius:0.625rem;cursor:not-allowed;"
                          title="Download unavailable — no matching parish record found. Contact the parish office.">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Unavailable
                    </span>
                    @else
                    {{-- Processing / pending verification --}}
                    <span style="display:inline-flex;align-items:center;gap:6px;background:#f1f5f9;color:#64748b;font-size:0.8125rem;font-weight:600;padding:0.5rem 1.125rem;border-radius:0.625rem;">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Processing…
                    </span>
                    @endif

                    {{-- Request Correction — only for verified, not yet released --}}
                    @if($verStatus === 'verified' && $cert->status !== 'released')
                    <a href="{{ route('parishioner.certificates.edit-request.create', $cert) }}"
                       style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;color:#16a34a;font-weight:600;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;text-decoration:none;border:1px solid #bbf7d0;transition:all 0.2s;"
                       onmouseover="this.style.background='#dcfce7';" onmouseout="this.style.background='#f0fdf4';">
                        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Request Correction
                    </a>
                    @endif

                    {{-- Cancel request — only while draft --}}
                    @if($cert->status === 'draft')
                    <a href="{{ route('parishioner.certificates.cancel', $cert) }}"
                       style="display:inline-flex;align-items:center;gap:6px;background:#fff;color:#ef4444;font-weight:600;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;text-decoration:none;border:1px solid #fca5a5;transition:all 0.2s;"
                       onmouseover="this.style.background='#fef2f2';" onmouseout="this.style.background='#fff';"
                       onclick="return confirm('Cancel this certificate request? This cannot be undone.')">
                        <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Cancel Request
                    </a>
                    @endif

                    {{-- QR Verify link --}}
                    @if($cert->qrCode)
                    <a href="{{ $cert->qrCode->verification_url }}" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;background:#f8faff;color:#64748b;font-weight:600;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;text-decoration:none;border:1px solid #e2e8f0;transition:all 0.2s;"
                       onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#f8faff';">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Verify QR
                    </a>
                    @endif

                </div>

                {{-- Status stepper --}}
                @php
                    $steps = [
                        ['key'=>'draft',    'label'=>'Submitted',   'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['key'=>'verified', 'label'=>'Verified',    'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['key'=>'issued',   'label'=>'Processing',  'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['key'=>'released', 'label'=>'Ready',       'icon'=>'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'],
                    ];
                    $currentStep = match($cert->status) {
                        'draft'    => 0,
                        'issued'   => $verStatus === 'verified' ? 2 : 1,
                        'released' => 3,
                        default    => 0,
                    };
                    if ($verStatus === 'verified' && $cert->status === 'draft') $currentStep = 1;
                @endphp
                <div style="display:flex;align-items:center;gap:0;padding-top:1rem;width:100%;overflow-x:auto;">
                    @foreach($steps as $si => $step)
                    @php $done = $si < $currentStep; $active = $si === $currentStep; @endphp
                    <div style="display:flex;align-items:center;flex:1;min-width:0;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:3px;flex-shrink:0;">
                            <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.65rem;
                                background:{{ $done ? '#2563eb' : ($active ? '#dbeafe' : '#f1f5f9') }};
                                border:2px solid {{ $done ? '#2563eb' : ($active ? '#2563eb' : '#e2e8f0') }};
                                color:{{ $done ? '#fff' : ($active ? '#2563eb' : '#94a3b8') }};">
                                @if($done)
                                <svg style="width:12px;height:12px;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @else
                                <svg style="width:11px;height:11px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/></svg>
                                @endif
                            </div>
                            <span style="font-size:0.62rem;font-weight:{{ $active ? '700' : '500' }};color:{{ $active ? '#1d4ed8' : ($done ? '#374151' : '#94a3b8') }};white-space:nowrap;">{{ $step['label'] }}</span>
                        </div>
                        @if(!$loop->last)
                        <div style="flex:1;height:2px;margin:0 3px;background:{{ $done ? '#2563eb' : '#e2e8f0' }};min-width:8px;"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div>{{ $certificates->links() }}</div>
    @endif

    {{-- Help card --}}
    <div style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fde68a;border-radius:1.25rem;padding:1.5rem;display:flex;align-items:flex-start;gap:1rem;">
        <div style="width:44px;height:44px;background:#f59e0b;border-radius:0.875rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg style="width:22px;height:22px;color:#fff;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <h3 style="font-weight:700;font-size:0.9375rem;color:#0f172a;margin:0 0 4px;">Need a Certificate?</h3>
            <p style="font-size:0.875rem;color:#64748b;margin:0 0 0.875rem;line-height:1.6;">
                Request a baptismal, confirmation, marriage, or other parish certificate by booking a service or contacting the parish office.
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:0.625rem;">
                <a href="{{ route('parishioner.certificates.create') }}"
                   style="display:inline-flex;align-items:center;gap:6px;background:#d97706;color:#fff;font-weight:700;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;text-decoration:none;transition:background 0.15s;"
                   onmouseover="this.style.background='#b45309';" onmouseout="this.style.background='#d97706';">
                    Request Online
                </a>
                <a href="{{ route('parishioner.bookings.create') }}"
                   style="display:inline-flex;align-items:center;gap:6px;background:#fff;color:#92400e;font-weight:600;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;text-decoration:none;border:1px solid #fde68a;transition:background 0.15s;"
                   onmouseover="this.style.background='#fef3c7';" onmouseout="this.style.background='#fff';">
                    Book a Service
                </a>
                <a href="{{ route('contact') }}"
                   style="display:inline-flex;align-items:center;gap:6px;background:#fff;color:#92400e;font-weight:600;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;text-decoration:none;border:1px solid #fde68a;transition:background 0.15s;"
                   onmouseover="this.style.background='#fef3c7';" onmouseout="this.style.background='#fff';">
                    Contact Parish Office
                </a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
<script>
(function () {
    const input   = document.getElementById('cert-search-input');
    const form    = document.getElementById('cert-search-form');
    const spinner = document.getElementById('cert-spinner');
    if (!input || !form) return;

    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        if (spinner) spinner.style.display = 'inline-block';
        timer = setTimeout(function () {
            form.submit();
        }, 300);
    });

    window.addEventListener('pageshow', function () {
        if (spinner) spinner.style.display = 'none';
    });
})();
</script>
@endpush
