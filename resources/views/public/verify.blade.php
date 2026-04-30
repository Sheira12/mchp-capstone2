@extends('layouts.public')

@section('title', 'Document Verification')
@section('meta-description', 'Verify the authenticity of a parish document from Mary Help of Christians Parish')

@push('styles')
<style>
.verify-card {
    background: #fff;
    border-radius: 1.25rem;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    overflow: hidden;
    max-width: 560px;
    margin: 0 auto;
}
.verify-header-valid {
    background: linear-gradient(135deg, #059669, #10b981);
    padding: 2rem 2rem 1.5rem;
    color: #fff;
    text-align: center;
}
.verify-header-invalid {
    background: linear-gradient(135deg, #dc2626, #ef4444);
    padding: 2rem 2rem 1.5rem;
    color: #fff;
    text-align: center;
}
.verify-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1rem;
}
.verify-icon svg { width: 36px; height: 36px; }
.detail-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.875rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.detail-row:last-child { border-bottom: none; }
.detail-label { font-size: 0.8rem; color: #64748b; font-weight: 500; flex-shrink: 0; }
.detail-value { font-size: 0.875rem; color: #0f172a; font-weight: 600; text-align: right; }
.scan-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    color: #166534; font-size: 0.75rem; font-weight: 600;
    padding: 4px 12px; border-radius: 9999px;
}
</style>
@endpush

@section('content')
<div style="min-height:80vh;background:#f0f4f8;padding:3rem 1rem;display:flex;align-items:center;">
<div style="width:100%;max-width:560px;margin:0 auto;">

    {{-- Parish branding --}}
    <div style="text-align:center;margin-bottom:1.5rem;">
        <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo"
             style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:3px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,0.15);margin-bottom:0.75rem;">
        <p style="font-size:0.875rem;font-weight:700;color:#1e3a8a;margin:0;">Mary Help of Christians Parish</p>
        <p style="font-size:0.75rem;color:#64748b;margin:2px 0 0;">Official Document Verification System</p>
    </div>

    <div class="verify-card">

        @if($valid)
        {{-- ── VALID ── --}}
        <div class="verify-header-valid">
            <div class="verify-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 style="font-size:1.375rem;font-weight:800;margin:0 0 4px;">Document Verified ✓</h1>
            <p style="color:rgba(255,255,255,0.85);font-size:0.875rem;margin:0;">
                This document is <strong>authentic</strong> and issued by the parish
            </p>
        </div>

        <div style="padding:1.5rem 2rem;">

            {{-- Document type badge --}}
            @php
                $typeIcons = [
                    'Certificate' => '📜',
                    'Booking'     => '📅',
                ];
                $icon = $typeIcons[$data['type']] ?? '📄';
            @endphp
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:2px solid #f0fdf4;">
                <div style="width:48px;height:48px;background:#f0fdf4;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">
                    {{ $icon }}
                </div>
                <div>
                    <p style="font-size:0.7rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#64748b;margin:0 0 2px;">{{ $data['type'] }}</p>
                    <p style="font-size:1rem;font-weight:800;color:#0f172a;margin:0;">
                        @if(isset($data['certificate_type']))
                            {{ $data['certificate_type'] }}
                        @elseif(isset($data['service']))
                            {{ $data['service'] }}
                        @else
                            Parish Document
                        @endif
                    </p>
                </div>
                <div style="margin-left:auto;">
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#d1fae5;color:#065f46;font-size:0.75rem;font-weight:700;padding:4px 12px;border-radius:9999px;">
                        ✓ {{ $data['status'] ?? 'Valid' }}
                    </span>
                </div>
            </div>

            {{-- Details --}}
            <div>
                @foreach($data as $key => $value)
                @if(!in_array($key, ['type','scan_count','last_scanned']) && $value)
                <div class="detail-row">
                    <span class="detail-label">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                    <span class="detail-value">{{ $value }}</span>
                </div>
                @endif
                @endforeach
            </div>

            {{-- Scan info --}}
            <div style="margin-top:1.25rem;padding:1rem;background:#f8faff;border-radius:0.875rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
                <div class="scan-badge">
                    <svg style="width:13px;height:13px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Scanned {{ $qrCode->scan_count }} {{ Str::plural('time', $qrCode->scan_count) }}
                </div>
                @if($qrCode->last_scanned_at)
                <span style="font-size:0.75rem;color:#94a3b8;">
                    Last: {{ $qrCode->last_scanned_at->diffForHumans() }}
                </span>
                @endif
            </div>

            {{-- Verified by --}}
            <div style="margin-top:1rem;text-align:center;padding-top:1rem;border-top:1px solid #f1f5f9;">
                <p style="font-size:0.75rem;color:#94a3b8;margin:0;">
                    Verified by <strong style="color:#1e3a8a;">{{ config('parish.name') }}</strong>
                    · Diocese of San Pablo
                </p>
                <p style="font-size:0.7rem;color:#cbd5e1;margin:3px 0 0;">
                    Verification ID: {{ $qrCode->token }}
                </p>
            </div>
        </div>

        @else
        {{-- ── INVALID ── --}}
        <div class="verify-header-invalid">
            <div class="verify-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 style="font-size:1.375rem;font-weight:800;margin:0 0 4px;">Verification Failed ✗</h1>
            <p style="color:rgba(255,255,255,0.85);font-size:0.875rem;margin:0;">
                {{ $message ?? 'This document could not be verified.' }}
            </p>
        </div>

        <div style="padding:2rem;text-align:center;">
            <div style="width:64px;height:64px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                <svg style="width:32px;height:32px;color:#dc2626;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <p style="font-size:0.9375rem;color:#374151;margin:0 0 0.5rem;font-weight:600;">Document Not Found</p>
            <p style="font-size:0.875rem;color:#64748b;margin:0 0 1.5rem;line-height:1.6;">
                This QR code is invalid, expired, or the document has been revoked.
                If you believe this is an error, please contact the parish office.
            </p>
            <a href="{{ route('contact') }}"
               style="display:inline-flex;align-items:center;gap:8px;background:#dc2626;color:#fff;font-weight:700;font-size:0.875rem;padding:0.75rem 1.75rem;border-radius:0.875rem;text-decoration:none;">
                Contact Parish Office
            </a>
        </div>
        @endif

    </div>

    {{-- Back link --}}
    <div style="text-align:center;margin-top:1.5rem;">
        <a href="{{ route('home') }}"
           style="font-size:0.875rem;color:#2563eb;text-decoration:none;display:inline-flex;align-items:center;gap:6px;"
           onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Return to Parish Website
        </a>
    </div>

</div>
</div>
@endsection
