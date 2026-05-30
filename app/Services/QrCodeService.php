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
        // Remove any existing QR for this booking
        $existing = $booking->qrCode;
        if ($existing) {
            if ($existing->qr_image_path) {
                Storage::disk('public')->delete($existing->qr_image_path);
            }
            $existing->delete();
        }

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
        if ($existing) {
            if ($existing->qr_image_path) {
                Storage::disk('public')->delete($existing->qr_image_path);
            }
            $existing->delete();
        }

        $qrCode = QrCode::create([
            'qr_codeable_type' => Certificate::class,
            'qr_codeable_id'   => $certificate->id,
        ]);

        $this->generateImage($qrCode);

        return $qrCode;
    }

    /**
     * Generate SVG QR code — no imagick extension required.
     * SVG is served directly in <img> tags in the browser.
     * For PDF embedding, CertificateService converts SVG to base64 data URI.
     */
    public function generateImage(QrCode $qrCode): void
    {
        $type = class_basename($qrCode->qr_codeable_type);
        $path = "qrcodes/{$type}/{$qrCode->token}.svg";

        $svg = QrCodeGenerator::format('svg')
            ->size(200)
            ->margin(1)
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
