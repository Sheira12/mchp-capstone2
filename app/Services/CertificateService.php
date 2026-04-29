<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\QrCode;
use App\Models\SacramentalRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class CertificateService
{
    public function generate(Certificate $certificate): Certificate
    {
        $certificate->load(['parishioner', 'sacramentalRecord', 'issuedBy']);

        // Generate or retrieve QR code
        $qrCode = $certificate->qrCode ?? $this->createQrCode($certificate);

        // Generate QR image
        $qrImagePath = "certificates/qr/{$certificate->certificate_number}.svg";
        $qrSvg = QrCodeGenerator::format('svg')
            ->size(150)
            ->errorCorrection('H')
            ->generate($qrCode->verification_url);

        Storage::disk('public')->put($qrImagePath, $qrSvg);
        $qrCode->update(['qr_image_path' => $qrImagePath]);

        // Generate PDF
        $view = $this->getTemplateView($certificate->type);
        $pdf  = Pdf::loadView($view, [
            'certificate' => $certificate,
            'qrCode'      => $qrCode,
            'qrImageUrl'  => Storage::disk('public')->url($qrImagePath),
            'parish'      => [
                'name'    => config('parish.name'),
                'address' => config('parish.address'),
                'phone'   => config('parish.phone'),
                'priest'  => config('parish.priest'),
            ],
        ])->setPaper('letter', 'portrait');

        $pdfPath = "certificates/pdf/{$certificate->certificate_number}.pdf";
        Storage::disk('public')->put($pdfPath, $pdf->output());

        $certificate->update([
            'file_path'    => $pdfPath,
            'qr_code_path' => $qrImagePath,
            'status'       => 'issued',
        ]);

        return $certificate->fresh();
    }

    public function autoGenerate(SacramentalRecord $record): ?Certificate
    {
        $typeMap = [
            'baptism'         => 'baptism',
            'confirmation'    => 'confirmation',
            'marriage'        => 'marriage',
            'first_communion' => 'first_communion',
        ];

        if (!isset($typeMap[$record->type])) return null;

        $certificate = Certificate::create([
            'parishioner_id'        => $record->parishioner_id,
            'sacramental_record_id' => $record->id,
            'type'                  => $typeMap[$record->type],
            'issued_date'           => now()->toDateString(),
            'issued_by'             => auth()->id(),
            'purpose'               => 'Auto-generated upon record creation',
        ]);

        return $this->generate($certificate);
    }

    public function batchPdf($certificates)
    {
        $pdf = Pdf::loadView('certificates.batch', [
            'certificates' => $certificates,
            'parish'       => [
                'name'    => config('parish.name'),
                'address' => config('parish.address'),
                'phone'   => config('parish.phone'),
                'priest'  => config('parish.priest'),
            ],
        ])->setPaper('letter', 'portrait');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="batch-certificates.pdf"',
        ]);
    }

    private function createQrCode(Certificate $certificate): QrCode
    {
        return QrCode::create([
            'qr_codeable_type' => Certificate::class,
            'qr_codeable_id'   => $certificate->id,
        ]);
    }

    private function getTemplateView(string $type): string
    {
        $views = [
            'baptism'         => 'certificates.baptism',
            'confirmation'    => 'certificates.confirmation',
            'marriage'        => 'certificates.marriage',
            'first_communion' => 'certificates.first-communion',
            'death_burial'    => 'certificates.death-burial',
            'no_impediment'   => 'certificates.no-impediment',
            'membership'      => 'certificates.membership',
        ];

        return $views[$type] ?? 'certificates.generic';
    }
}
