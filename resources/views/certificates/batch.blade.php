<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8">
@include('certificates._premium_css')
<style>
/* Batch: each cert-page is one physical page */
.cert-page { page-break-after: always; }
.cert-page:last-child { page-break-after: avoid; }
/* Border SVG must reset per page in batch mode */
.border-wrap { position: relative; width: 595pt; height: 842pt; overflow: hidden; }
.border-wrap .border-svg { position: absolute; top: 0; left: 0; }
.border-wrap .wm       { position: absolute; top: 50%; left: 50%;
                          width: 220pt; height: 260pt;
                          margin-top: -130pt; margin-left: -110pt; }
</style>
</head>
<body>
@php
$divSvg = '<svg width="420" height="12" viewBox="0 0 420 12" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="gL"><stop offset="0%" stop-color="#D4AF37" stop-opacity="0"/><stop offset="100%" stop-color="#D4AF37"/></linearGradient><linearGradient id="gR" x1="100%" y1="0" x2="0" y2="0"><stop offset="0%" stop-color="#D4AF37" stop-opacity="0"/><stop offset="100%" stop-color="#D4AF37"/></linearGradient></defs><line x1="0" y1="6" x2="178" y2="6" stroke="url(#gL)" stroke-width="0.8"/><polygon points="188,6 193,2 198,6 193,10" fill="#D4AF37"/><polygon points="204,6 208,3.5 212,6 208,8.5" fill="#D4AF37"/><polygon points="218,6 223,2 228,6 223,10" fill="#D4AF37"/><line x1="238" y1="6" x2="420" y2="6" stroke="url(#gR)" stroke-width="0.8"/></svg>';
$divSmSvg = '<svg width="200" height="8" viewBox="0 0 200 8" xmlns="http://www.w3.org/2000/svg"><line x1="0" y1="4" x2="85" y2="4" stroke="#D4AF37" stroke-width="0.6" opacity="0.6"/><circle cx="100" cy="4" r="2.5" fill="#D4AF37"/><line x1="115" y1="4" x2="200" y2="4" stroke="#D4AF37" stroke-width="0.6" opacity="0.6"/></svg>';
@endphp

@foreach($certData as $item)
@php
$certificate = $item['certificate'];
$qrBase64    = $item['qrBase64'];
$nameLen = strlen($certificate->parishioner->full_name ?? '');
$nameCls = $nameLen > 36 ? 'xl' : ($nameLen > 26 ? 'lg' : '');
@endphp

{{-- Wrap so border SVG is scoped to this page --}}
<div class="border-wrap">
    {{-- Border SVG --}}
    @include('certificates._border')

    <table class="cert-page" cellpadding="0" cellspacing="0">

        <tr class="row-header">
            <td class="pad-l"></td>
            <td class="col-c">
                <div class="header-inner">
                    <div class="diocese-lbl">Diocese of San Pablo &nbsp;·&nbsp; Archdiocese of Lipa</div>
                    <div class="seal-wrap">
                        @if(file_exists($logoPath))
                            <img src="{{ $logoPath }}" alt="Parish Seal">
                        @else
                            <svg width="66" height="66" viewBox="0 0 66 66" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="33" cy="33" r="31" stroke="#D4AF37" stroke-width="2"/>
                                <circle cx="33" cy="33" r="26" stroke="#D4AF37" stroke-width="0.5" opacity="0.5"/>
                                <text x="33" y="30" text-anchor="middle" font-family="Georgia,serif" font-size="10" font-weight="bold" fill="#1F3A5F">MHC</text>
                                <text x="33" y="42" text-anchor="middle" font-family="Georgia,serif" font-size="6" fill="#D4AF37">✞</text>
                            </svg>
                        @endif
                    </div>
                    <div class="parish-name">Mary Help of Christians Parish</div>
                    <div class="parish-addr">Southville 1, Niugan, Cabuyao, Laguna &nbsp;·&nbsp; Diocese of San Pablo</div>
                    <div class="gold-div">{!! $divSvg !!}</div>
                </div>
            </td>
            <td class="pad-r"></td>
        </tr>

        <tr class="row-title">
            <td class="pad-l"></td>
            <td class="col-c">
                <div class="title-inner">
                    <div class="cert-label">This Certifies That</div>
                    <div class="cert-title">{{ $certificate->getTypeLabel() }}</div>
                    <div class="cert-sub">Official Parish Certificate &nbsp;·&nbsp; Certificatum Officiale</div>
                    <div class="gold-div-sm">{!! $divSmSvg !!}</div>
                    <div class="cert-intro">
                        Be it known to all that the following person has been issued<br>
                        this official certificate by the parish office
                    </div>
                </div>
            </td>
            <td class="pad-r"></td>
        </tr>

        <tr class="row-recipient">
            <td class="pad-l"></td>
            <td class="col-c">
                <div class="recipient-inner">
                    <div class="recipient-name {{ $nameCls }}">{{ $certificate->parishioner->full_name }}</div>
                    <div class="recipient-role">{{ $certificate->getTypeLabel() }}</div>
                </div>
            </td>
            <td class="pad-r"></td>
        </tr>

        <tr class="row-details">
            <td class="pad-l"></td>
            <td class="col-c">
                <div class="details-inner">
                    <table class="det-tbl" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="det-left">
                                <div class="det-item">
                                    <div class="det-lbl">Certificate Number</div>
                                    <div class="det-val">{{ $certificate->certificate_number }}</div>
                                </div>
                                <div class="det-item">
                                    <div class="det-lbl">Certificate Type</div>
                                    <div class="det-val">{{ $certificate->getTypeLabel() }}</div>
                                </div>
                                <div class="det-item">
                                    <div class="det-lbl">Date of Birth</div>
                                    <div class="det-val {{ $certificate->parishioner->birthdate ? '' : 'na' }}">
                                        {{ $certificate->parishioner->birthdate?->format('F d, Y') ?? 'Not recorded' }}
                                    </div>
                                </div>
                                @if($certificate->sacramentalRecord)
                                <div class="det-item">
                                    <div class="det-lbl">Date Administered</div>
                                    <div class="det-val">{{ $certificate->sacramentalRecord->date_administered?->format('F d, Y') ?? '—' }}</div>
                                </div>
                                @endif
                            </td>
                            <td class="det-gap"></td>
                            <td class="det-right">
                                <div class="det-item">
                                    <div class="det-lbl">Issued Date</div>
                                    <div class="det-val">{{ $certificate->issued_date->format('F d, Y') }}</div>
                                </div>
                                <div class="det-item">
                                    <div class="det-lbl">Purpose</div>
                                    <div class="det-val {{ $certificate->purpose ? '' : 'na' }}">
                                        {{ $certificate->purpose ?? 'Official use' }}
                                    </div>
                                </div>
                                @if($certificate->sacramentalRecord)
                                <div class="det-item">
                                    <div class="det-lbl">Celebrant</div>
                                    <div class="det-val {{ $certificate->sacramentalRecord->celebrant ? '' : 'na' }}">
                                        {{ $certificate->sacramentalRecord->celebrant ?? 'Not recorded' }}
                                    </div>
                                </div>
                                @endif
                                <div class="det-item">
                                    <div class="det-lbl">Parish</div>
                                    <div class="det-val">{{ $parish['name'] }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="pad-r"></td>
        </tr>

        <tr class="row-issuance">
            <td class="pad-l"></td>
            <td class="col-c">
                <div class="issuance-inner">
                    Issued this <b>{{ $certificate->issued_date->format('jS') }}</b> day of
                    <b>{{ $certificate->issued_date->format('F Y') }}</b>, at
                    <b>Mary Help of Christians Parish</b>, Cabuyao, Laguna,
                    for the purpose of <b>{{ $certificate->purpose ?? 'official use' }}</b>.
                </div>
            </td>
            <td class="pad-r"></td>
        </tr>

        <tr class="row-spacer"><td colspan="3"></td></tr>

        <tr class="row-sig">
            <td class="pad-l"></td>
            <td class="col-c">
                <div class="sig-inner">
                    <table class="sig-tbl" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="sig-cell">
                                <div class="sig-line">
                                    <div class="sig-name">{{ $certificate->issuedBy?->name ?? 'Parish Secretary' }}</div>
                                    <div class="sig-title">Parish Secretary</div>
                                </div>
                            </td>
                            <td class="sig-cell">
                                <div class="seal-circ">
                                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin:12pt auto 0;display:block;">
                                        <circle cx="14" cy="14" r="11" stroke="#D4AF37" stroke-width="1.2"/>
                                        <path d="M14 8v12M8 14h12" stroke="#D4AF37" stroke-width="1.2"/>
                                    </svg>
                                </div>
                                <div class="seal-lbl">Official Seal</div>
                            </td>
                            <td class="sig-cell">
                                <div class="sig-line">
                                    <div class="sig-name">{{ $parish['priest'] }}</div>
                                    <div class="sig-title">Parish Priest</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="pad-r"></td>
        </tr>

        <tr class="row-footer">
            <td class="pad-l"></td>
            <td class="col-c">
                <div class="footer-inner">
                    <table class="ft-tbl" cellpadding="0" cellspacing="0">
                        <tr>
                            <td class="ft-left">
                                <div class="ft-certno-lbl">Certificate No.</div>
                                <div class="ft-certno-val">{{ $certificate->certificate_number }}</div>
                                <div class="ft-verify">Issued: {{ $certificate->issued_date->format('M d, Y') }}</div>
                            </td>
                            <td class="ft-center">
                                <div class="ft-contact">
                                    <span class="ft-parish-strong">{{ $parish['name'] }}</span><br>
                                    {{ $parish['address'] }}<br>
                                    Tel: {{ config('parish.phone') }} &nbsp;·&nbsp; {{ config('parish.email') }}
                                </div>
                                <div class="ft-verify">This document is electronically generated and verified via QR code.</div>
                            </td>
                            <td class="ft-right">
                                @if(!empty($qrBase64))
                                <div class="ft-qr">
                                    <img src="{{ $qrBase64 }}" alt="QR Verification">
                                    <div class="ft-qr-lbl">Scan to verify</div>
                                </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="pad-r"></td>
        </tr>

    </table>
</div>{{-- /.border-wrap --}}

@endforeach
</body>
</html>
