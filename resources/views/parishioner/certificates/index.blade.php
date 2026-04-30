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
    </div>

    @if($certificates->isEmpty())
    <div style="background:#fff;border-radius:1.25rem;border:1px solid #f1f5f9;box-shadow:0 2px 8px rgba(0,0,0,0.04);padding:4rem 2rem;text-align:center;">
        <div style="width:72px;height:72px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
            <svg style="width:36px;height:36px;color:#d97706;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 style="font-size:1.125rem;font-weight:700;color:#0f172a;margin:0 0 0.5rem;">No certificates yet</h3>
        <p style="font-size:0.875rem;color:#64748b;max-width:360px;margin:0 auto 1.5rem;line-height:1.6;">
            Certificates will appear here once issued by the parish office. Book a service to request one.
        </p>
        <a href="{{ route('parishioner.bookings.create') }}"
           style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;font-weight:700;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:0.875rem;text-decoration:none;box-shadow:0 4px 14px rgba(37,99,235,0.3);">
            <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Book a Service
        </a>
    </div>

    @else

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;">
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

            // Can download if file exists (issued or released)
            $canDownload = $cert->file_path && in_array($cert->status, ['issued','released']);
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
                    @else
                    <span style="display:inline-flex;align-items:center;gap:6px;background:#f1f5f9;color:#64748b;font-size:0.8125rem;font-weight:600;padding:0.5rem 1.125rem;border-radius:0.625rem;">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Processing…
                    </span>
                    @endif

                    {{-- QR Verify link --}}
                    @if($cert->qrCode)
                    <a href="{{ $cert->qrCode->verification_url }}" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;color:#16a34a;font-weight:600;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;text-decoration:none;border:1px solid #bbf7d0;transition:all 0.2s;"
                       onmouseover="this.style.background='#dcfce7';" onmouseout="this.style.background='#f0fdf4';">
                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Verify
                    </a>
                    @endif

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
                <a href="{{ route('parishioner.bookings.create') }}"
                   style="display:inline-flex;align-items:center;gap:6px;background:#d97706;color:#fff;font-weight:700;font-size:0.8125rem;padding:0.5rem 1.125rem;border-radius:0.625rem;text-decoration:none;transition:background 0.15s;"
                   onmouseover="this.style.background='#b45309';" onmouseout="this.style.background='#d97706';">
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
