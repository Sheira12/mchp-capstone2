<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; color: #1a1a1a; background: #fff; }
        .page { width: 8.5in; min-height: 11in; padding: 0.75in; position: relative; }
        .border-outer { border: 4px double #1a3a6b; padding: 20px; min-height: 9.5in; }
        .border-inner { border: 1px solid #1a3a6b; padding: 20px; min-height: 9.3in; }
        .header { text-align: center; margin-bottom: 20px; }
        .diocese { font-size: 11pt; color: #555; letter-spacing: 2px; text-transform: uppercase; }
        .parish-name { font-size: 18pt; font-weight: bold; color: #1a3a6b; margin: 5px 0; }
        .parish-address { font-size: 10pt; color: #555; }
        .divider { border: none; border-top: 2px solid #1a3a6b; margin: 15px 0; }
        .cert-title { text-align: center; font-size: 22pt; font-weight: bold; color: #1a3a6b; letter-spacing: 3px; text-transform: uppercase; margin: 20px 0 5px; }
        .cert-subtitle { text-align: center; font-size: 11pt; color: #555; margin-bottom: 25px; }
        .body-text { font-size: 12pt; line-height: 2; text-align: justify; margin-bottom: 15px; }
        .field { display: inline-block; border-bottom: 1px solid #333; min-width: 200px; text-align: center; font-weight: bold; padding: 0 5px; }
        .details-table { width: 100%; margin: 20px 0; border-collapse: collapse; }
        .details-table td { padding: 6px 10px; font-size: 11pt; }
        .details-table .label { color: #555; width: 40%; }
        .details-table .value { font-weight: bold; border-bottom: 1px solid #ccc; }
        .signature-section { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-block { text-align: center; width: 45%; }
        .signature-line { border-top: 1px solid #333; margin-top: 40px; padding-top: 5px; font-size: 10pt; }
        .cert-number { font-size: 9pt; color: #888; margin-top: 10px; }
        .qr-section { position: absolute; bottom: 1in; right: 0.75in; text-align: center; }
        .qr-section img { width: 80px; height: 80px; }
        .qr-section p { font-size: 7pt; color: #888; margin-top: 3px; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 60pt; color: rgba(26,58,107,0.05); font-weight: bold; white-space: nowrap; pointer-events: none; }
    </style>
</head>
<body>
<div class="page">
    <div class="border-outer">
        <div class="border-inner">
            <div class="watermark">MARY HELP OF CHRISTIANS</div>
            <div class="header">
                <p class="diocese">Diocese of San Pablo</p>
                <p class="parish-name">{{ $parish['name'] }}</p>
                <p class="parish-address">{{ $parish['address'] }}</p>
                <p class="parish-address">Tel: {{ config('parish.phone') }} | Email: {{ config('parish.email') }}</p>
            </div>
            <hr class="divider">
            <div class="cert-title">Certificate of Confirmation</div>
            <div class="cert-subtitle">Katibayan ng Kumpil</div>
            <p class="body-text">
                This is to certify that <span class="field">{{ $certificate->parishioner->full_name }}</span>
                received the Sacrament of <strong>Confirmation</strong> in this parish on
                <span class="field">{{ $certificate->sacramentalRecord?->date_administered?->format('F d, Y') ?? '___________________' }}</span>.
            </p>
            <table class="details-table">
                <tr>
                    <td class="label">Date of Birth:</td>
                    <td class="value">{{ $certificate->parishioner->birthdate?->format('F d, Y') ?? '—' }}</td>
                    <td class="label">Gender:</td>
                    <td class="value">{{ ucfirst($certificate->parishioner->gender ?? '—') }}</td>
                </tr>
                <tr>
                    <td class="label">Celebrant / Bishop:</td>
                    <td class="value">{{ $certificate->sacramentalRecord?->celebrant ?? '—' }}</td>
                    <td class="label">Venue:</td>
                    <td class="value">{{ $certificate->sacramentalRecord?->venue ?? $parish['name'] }}</td>
                </tr>
                <tr>
                    <td class="label">Sponsor (Ninong/Ninang):</td>
                    <td class="value">{{ $certificate->sacramentalRecord?->sponsors[0] ?? ($certificate->sacramentalRecord?->godparents[0] ?? '—') }}</td>
                    <td class="label">Confirmation Name:</td>
                    <td class="value">—</td>
                </tr>
                <tr>
                    <td class="label">Register No.:</td>
                    <td class="value">{{ $certificate->sacramentalRecord?->register_number ?? '—' }}</td>
                    <td class="label">Page / Line:</td>
                    <td class="value">{{ $certificate->sacramentalRecord?->page_number ?? '—' }} / {{ $certificate->sacramentalRecord?->line_number ?? '—' }}</td>
                </tr>
            </table>
            <p class="body-text" style="margin-top: 20px;">
                Issued this <span class="field">{{ $certificate->issued_date->format('d') }}</span> day of
                <span class="field">{{ $certificate->issued_date->format('F Y') }}</span>
                for the purpose of <span class="field">{{ $certificate->purpose ?? 'official use' }}</span>.
            </p>
            <div class="signature-section">
                <div class="signature-block">
                    <div class="signature-line"><strong>{{ $certificate->issuedBy?->name ?? 'Parish Secretary' }}</strong><br>Parish Secretary</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line"><strong>{{ $parish['priest'] }}</strong><br>Parish Priest</div>
                </div>
            </div>
            <p class="cert-number" style="text-align:center; margin-top: 20px;">Certificate No.: {{ $certificate->certificate_number }}</p>
            <div class="qr-section">
                <img src="{{ $qrImageUrl }}" alt="QR Code">
                <p>Scan to verify</p>
                <p>{{ $qrCode->token }}</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
