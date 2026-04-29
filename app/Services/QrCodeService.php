<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Certificate;
use App\Models\QrCode;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class QrCodeService
{
    public function generateForBooking(Booking $booking): QrCode
    {
        $qrCode = QrCode::create([
            'qr_codeable_type' => Booking::class,
            'qr_codeable_id'   => $booking->id,
        ]);

        $this->generateImage($qrCode);

        return $qrCode;
    }

    public function generateForCertificate(Certificate $certificate): QrCode
    {
        $existing = $certificate->qrCode;
        if ($existing) return $existing;

        $qrCode = QrCode::create([
            'qr_codeable_type' => Certificate::class,
            'qr_codeable_id'   => $certificate->id,
        ]);

        $this->generateImage($qrCode);

        return $qrCode;
    }

    public function generateImage(QrCode $qrCode): void
    {
        $type = class_basename($qrCode->qr_codeable_type);
        $path = "qrcodes/{$type}/{$qrCode->token}.svg";

        $svg = QrCodeGenerator::format('svg')
            ->size(200)
            ->errorCorrection('H')
            ->generate($qrCode->verification_url);

        Storage::disk('public')->put($path, $svg);
        $qrCode->update(['qr_image_path' => $path]);
    }

    public function deactivate(QrCode $qrCode): void
    {
        $qrCode->update(['is_active' => false]);
    }
}
