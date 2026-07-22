<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('certificates._premium_css')
</head>
<body>
@php
$nameLen = strlen($certificate->parishioner->full_name ?? '');
$nameCls = $nameLen > 36 ? 'xl' : ($nameLen > 26 ? 'lg' : '');
$orn   = '<svg width="370" height="10" viewBox="0 0 370 10" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="gl"><stop offset="0%" stop-color="#D4AF37" stop-opacity="0"/><stop offset="100%" stop-color="#D4AF37"/></linearGradient><linearGradient id="gr" x1="1" x2="0" y1="0" y2="0"><stop offset="0%" stop-color="#D4AF37" stop-opacity="0"/><stop offset="100%" stop-color="#D4AF37"/></linearGradient></defs><line x1="0" y1="5" x2="153" y2="5" stroke="url(#gl)" stroke-width="0.8"/><polygon points="161,5 165,2 169,5 165,8" fill="#D4AF37"/><polygon points="179,5 183,3 187,5 183,7" fill="#D4AF37"/><polygon points="197,5 201,2 205,5 201,8" fill="#D4AF37"/><line x1="213" y1="5" x2="370" y2="5" stroke="url(#gr)" stroke-width="0.8"/></svg>';
$ornSm = '<svg width="130" height="7" viewBox="0 0 130 7" xmlns="http://www.w3.org/2000/svg"><line x1="0" y1="3.5" x2="55" y2="3.5" stroke="#D4AF37" stroke-width="0.6" opacity="0.5"/><circle cx="65" cy="3.5" r="2" fill="#D4AF37"/><line x1="75" y1="3.5" x2="130" y2="3.5" stroke="#D4AF37" stroke-width="0.6" opacity="0.5"/></svg>';
@endphp
<div class="border-outer"></div><div class="border-navy"></div><div class="border-gold"></div>
<div class="cert-content">
    <div class="cert-header">
        <div class="diocese-bar">Diocese of San Pablo &nbsp;&nbsp;·&nbsp;&nbsp; Archdiocese of Lipa</div>
        <div class="seal-ring">@if(file_exists($logoPath))<img src="{{ $logoPath }}" alt="Seal">@else<svg width="66" height="66" viewBox="0 0 66 66" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="33" cy="33" r="31" stroke="#D4AF37" stroke-width="2"/><circle cx="33" cy="33" r="25" stroke="#D4AF37" stroke-width="0.5" opacity="0.4"/><text x="33" y="30" text-anchor="middle" font-family="Georgia,serif" font-size="9" font-weight="bold" fill="#1F3A5F">MHC</text><text x="33" y="41" text-anchor="middle" font-family="Georgia,serif" font-size="7" fill="#D4AF37">PARISH</text></svg>@endif</div>
        <span class="parish-name">Mary Help of Christians Parish</span>
        <span class="parish-addr">Southville 1, Niugan, Cabuyao, Laguna &nbsp;·&nbsp; Diocese of San Pablo</span>
    </div>
    <div class="cert-title-wrap">
        <span class="gold-orn">{!! $orn !!}</span>
        <span class="cert-label">This Certifies That</span>
        <span class="cert-title">Certificate of Death &amp; Burial</span>
        <span class="cert-sub">Katibayan ng Kamatayan at Libing &nbsp;·&nbsp; Exsequiae</span>
        <span class="gold-orn-sm">{!! $ornSm !!}</span>
        <span class="cert-intro">Be it known to all that the following person has been given<br>the Christian Burial rites of the Holy Catholic Church</span>
    </div>
    <div class="recipient-wrap">
        <span class="recipient-name {{ $nameCls }}">{{ $certificate->parishioner->full_name }}</span>
        <span class="recipient-role">Departed Soul &nbsp;·&nbsp; Eternal Rest Grant Unto Them</span>
    </div>
    <div class="details-wrap"><table class="details-tbl" cellpadding="0" cellspacing="0"><tr>
        <td class="det-left">
            <div class="det-item"><span class="det-lbl">Date of Burial</span><span class="det-val {{ $certificate->sacramentalRecord?->date_administered ? '' : 'na' }}">{{ $certificate->sacramentalRecord?->date_administered?->format('F d, Y') ?? 'Not recorded' }}</span></div>
            <div class="det-item"><span class="det-lbl">Date of Birth</span><span class="det-val {{ $certificate->parishioner->birthdate ? '' : 'na' }}">{{ $certificate->parishioner->birthdate?->format('F d, Y') ?? 'Not recorded' }}</span></div>
            <div class="det-item"><span class="det-lbl">Age at Death</span><span class="det-val">{{ $certificate->parishioner->age ?? '—' }}</span></div>
            <div class="det-item"><span class="det-lbl">Address</span><span class="det-val {{ $certificate->parishioner->address ? '' : 'na' }}">{{ $certificate->parishioner->address ?? 'Not recorded' }}@if($certificate->parishioner->barangay), Brgy. {{ $certificate->parishioner->barangay }}@endif</span></div>
        </td>
        <td class="det-gap"></td>
        <td class="det-right">
            <div class="det-item"><span class="det-lbl">Officiating Priest</span><span class="det-val {{ $certificate->sacramentalRecord?->celebrant ? '' : 'na' }}">{{ $certificate->sacramentalRecord?->celebrant ?? 'Not recorded' }}</span></div>
            <div class="det-item"><span class="det-lbl">Cemetery / Venue</span><span class="det-val">{{ $certificate->sacramentalRecord?->venue ?? $parish['name'] }}</span></div>
            <div class="det-item"><span class="det-lbl">Register No.</span><span class="det-val">{{ $certificate->sacramentalRecord?->register_number ?? '—' }}</span></div>
            <div class="det-item"><span class="det-lbl">Page / Line</span><span class="det-val">{{ $certificate->sacramentalRecord?->page_number ?? '—' }} / {{ $certificate->sacramentalRecord?->line_number ?? '—' }}</span></div>
        </td>
    </tr></table></div>
    <div class="issuance-wrap">Issued this <b>{{ $certificate->issued_date->format('jS') }}</b> day of <b>{{ $certificate->issued_date->format('F Y') }}</b>, at <b>Mary Help of Christians Parish</b>, Cabuyao, Laguna, for the purpose of <b>{{ $certificate->purpose ?? 'official use' }}</b>.</div>
    <div class="sig-wrap"><table class="sig-tbl" cellpadding="0" cellspacing="0"><tr>
        <td class="sig-cell"><div class="sig-line"><div class="sig-name">{{ $certificate->issuedBy?->name ?? 'Parish Secretary' }}</div><div class="sig-title">Parish Secretary</div></div></td>
        <td class="sig-cell"><div class="seal-circle"><svg width="26" height="26" viewBox="0 0 26 26" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin:12pt auto 0;display:block;"><circle cx="13" cy="13" r="10" stroke="#D4AF37" stroke-width="1.2"/><circle cx="13" cy="13" r="6" stroke="#D4AF37" stroke-width="0.5" opacity="0.5"/><path d="M13 8v10M8 13h10" stroke="#D4AF37" stroke-width="1.2"/></svg></div><div class="seal-label">Official Seal</div></td>
        <td class="sig-cell"><div class="sig-line"><div class="sig-name">{{ $parish['priest'] }}</div><div class="sig-title">Parish Priest</div></div></td>
    </tr></table></div>
    <div class="footer-wrap"><table class="ft-tbl" cellpadding="0" cellspacing="0"><tr>
        <td class="ft-left"><span class="ft-certno-lbl">Certificate No.</span><span class="ft-certno-val">{{ $certificate->certificate_number }}</span><span class="ft-issued">Issued: {{ $certificate->issued_date->format('M d, Y') }}</span></td>
        <td class="ft-center"><span class="ft-contact-name">{{ $parish['name'] }}</span><span class="ft-contact-detail">{{ $parish['address'] }}<br>Tel: {{ config('parish.phone') }} &nbsp;·&nbsp; {{ config('parish.email') }}</span><span class="ft-verify-note">Electronically generated. Scan QR to verify authenticity.</span></td>
        <td class="ft-right">@if(!empty($qrBase64))<div class="ft-qr"><img src="{{ $qrBase64 }}" alt="QR"><span class="ft-qr-lbl">Scan to verify</span></div>@endif</td>
    </tr></table></div>
</div>
</body>
</html>
