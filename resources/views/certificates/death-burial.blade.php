<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('certificates._premium_css')
</head>
<body>
@php
$cornerSvg = '<svg width="32pt" height="32pt" viewBox="0 0 32 32" fill="none"><path d="M2 2 L2 18 M2 2 L18 2" stroke="#D4AF37" stroke-width="2"/><path d="M2 2 L2 10 M2 2 L10 2" stroke="#1F3A5F" stroke-width="0.8"/><circle cx="2" cy="2" r="1.5" fill="#D4AF37"/><path d="M8 2 Q2 2 2 8" stroke="#D4AF37" stroke-width="0.8" fill="none"/></svg>';
$divSvg = '<svg width="420pt" height="12pt" viewBox="0 0 420 12" fill="none"><line x1="0" y1="6" x2="178" y2="6" stroke="url(#gl)" stroke-width="0.8"/><polygon points="188,6 193,2 198,6 193,10" fill="#D4AF37"/><polygon points="203,6 207,4 211,6 207,8" fill="#D4AF37"/><polygon points="216,6 221,2 226,6 221,10" fill="#D4AF37"/><line x1="236" y1="6" x2="420" y2="6" stroke="url(#gr)" stroke-width="0.8"/><defs><linearGradient id="gl" x1="0" y1="0" x2="178" y2="0" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#D4AF37" stop-opacity="0"/><stop offset="100%" stop-color="#D4AF37"/></linearGradient><linearGradient id="gr" x1="236" y1="0" x2="420" y2="0" gradientUnits="userSpaceOnUse"><stop offset="0%" stop-color="#D4AF37"/><stop offset="100%" stop-color="#D4AF37" stop-opacity="0"/></linearGradient></defs></svg>';
@endphp

@include('certificates._border')

<div class="cert-footer">
    <div class="ft-left"><div class="ft-certno-lbl">Certificate No.</div><div class="ft-certno-val">{{ $certificate->certificate_number }}</div></div>
    <div class="ft-center"><div class="ft-contact"><strong style="color:#1F3A5F;font-size:6.5pt;">{{ $parish['name'] }}</strong><br>{{ $parish['address'] }}<br>Tel: {{ config('parish.phone') }} &nbsp;·&nbsp; {{ config('parish.email') }}</div></div>
    <div class="ft-right">@if(!empty($qrBase64))<div class="ft-qr"><img src="{{ $qrBase64 }}" alt="QR"><div class="ft-qr-lbl">Scan to verify</div></div>@endif</div>
</div>

<div class="cert-body">
    <div class="diocese">Diocese of San Pablo &nbsp;·&nbsp; Archdiocese of Lipa</div>
    <div class="seal-ring">
        @if(file_exists($logoPath))<img src="{{ $logoPath }}" alt="Seal">
        @else<svg width="64pt" height="64pt" viewBox="0 0 64 64" fill="none"><circle cx="32" cy="32" r="30" stroke="#D4AF37" stroke-width="2"/><text x="32" y="38" text-anchor="middle" font-family="Georgia,serif" font-size="13" font-weight="bold" fill="#1F3A5F">MHC</text></svg>@endif
    </div>
    <div class="parish-name">Mary Help of Christians Parish</div>
    <div class="parish-addr">Southville 1, Niugan, Cabuyao, Laguna &nbsp;·&nbsp; Diocese of San Pablo</div>
    <div class="gold-div">{!! $divSvg !!}</div>
    <div class="cert-label">This Certifies That</div>
    <div class="cert-title">Certificate of Death &amp; Burial</div>
    <div class="cert-sub">Katibayan ng Kamatayan / Libing &nbsp;·&nbsp; Exsequiae</div>
    <div class="intro">Be it known to all that the following person has been given<br>the Christian Burial rites of the Holy Catholic Church</div>
    <div class="recipient-block">
        <div class="recipient-name">{{ $certificate->parishioner->full_name }}</div>
        <div class="recipient-role">Departed Soul &nbsp;·&nbsp; Eternal Rest</div>
    </div>
    <div class="det-grid">
        <div class="det-col">
            <div class="det-item"><div class="det-lbl">Date of Burial</div><div class="det-val {{ $certificate->sacramentalRecord?->date_administered ? '' : 'na' }}">{{ $certificate->sacramentalRecord?->date_administered?->format('F d, Y') ?? 'Not recorded' }}</div></div>
            <div class="det-item"><div class="det-lbl">Date of Birth</div><div class="det-val {{ $certificate->parishioner->birthdate ? '' : 'na' }}">{{ $certificate->parishioner->birthdate?->format('F d, Y') ?? 'Not recorded' }}</div></div>
            <div class="det-item"><div class="det-lbl">Age at Death</div><div class="det-val">{{ $certificate->parishioner->age ?? '—' }}</div></div>
            <div class="det-item"><div class="det-lbl">Address</div><div class="det-val {{ $certificate->parishioner->address ? '' : 'na' }}">{{ $certificate->parishioner->address ?? 'Not recorded' }}@if($certificate->parishioner->barangay), Brgy. {{ $certificate->parishioner->barangay }}@endif</div></div>
        </div>
        <div class="det-col">
            <div class="det-item"><div class="det-lbl">Officiating Priest</div><div class="det-val {{ $certificate->sacramentalRecord?->celebrant ? '' : 'na' }}">{{ $certificate->sacramentalRecord?->celebrant ?? 'Not recorded' }}</div></div>
            <div class="det-item"><div class="det-lbl">Venue</div><div class="det-val">{{ $certificate->sacramentalRecord?->venue ?? $parish['name'] }}</div></div>
            <div class="det-item"><div class="det-lbl">Register No.</div><div class="det-val">{{ $certificate->sacramentalRecord?->register_number ?? '—' }}</div></div>
            <div class="det-item"><div class="det-lbl">Page / Line</div><div class="det-val">{{ $certificate->sacramentalRecord?->page_number ?? '—' }} / {{ $certificate->sacramentalRecord?->line_number ?? '—' }}</div></div>
        </div>
    </div>
    <div class="issuance">Issued this <b>{{ $certificate->issued_date->format('jS') }}</b> day of <b>{{ $certificate->issued_date->format('F Y') }}</b> for the purpose of <b>{{ $certificate->purpose ?? 'official use' }}</b>.</div>
    <div class="sig-row">
        <div class="sig-cell"><div class="sig-line"><div class="sig-name">{{ $certificate->issuedBy?->name ?? 'Parish Secretary' }}</div><div class="sig-title">Parish Secretary</div></div></div>
        <div class="seal-cell"><div class="seal-circ"><svg width="24pt" height="24pt" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="#D4AF37" stroke-width="1.2"/><path d="M12 7v10M7 12h10" stroke="#D4AF37" stroke-width="1.2"/></svg></div><div class="seal-lbl">Official Seal</div></div>
        <div class="sig-cell"><div class="sig-line"><div class="sig-name">{{ $parish['priest'] }}</div><div class="sig-title">Parish Priest</div></div></div>
    </div>
</div>
</body>
</html>
