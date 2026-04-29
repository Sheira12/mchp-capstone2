<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Public QR code verification endpoint.
     */
    public function verify(string $token)
    {
        $qrCode = QrCode::where('token', $token)
            ->where('is_active', true)
            ->first();

        if (!$qrCode) {
            return view('public.verify', ['valid' => false, 'message' => 'Invalid or expired QR code.']);
        }

        $qrCode->incrementScanCount();

        $entity = $qrCode->qrCodeable;

        if (!$entity) {
            return view('public.verify', ['valid' => false, 'message' => 'Record not found.']);
        }

        $data = $this->buildVerificationData($entity, $qrCode);

        return view('public.verify', ['valid' => true, 'data' => $data, 'qrCode' => $qrCode]);
    }

    /**
     * API endpoint for mobile QR scanning.
     */
    public function apiVerify(string $token)
    {
        $qrCode = QrCode::where('token', $token)->where('is_active', true)->first();

        if (!$qrCode) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired QR code.'], 404);
        }

        $qrCode->incrementScanCount();
        $entity = $qrCode->qrCodeable;

        return response()->json([
            'valid' => true,
            'data'  => $this->buildVerificationData($entity, $qrCode),
        ]);
    }

    private function buildVerificationData($entity, QrCode $qrCode): array
    {
        $type = class_basename($entity);

        return match ($type) {
            'Certificate' => [
                'type'               => 'Certificate',
                'certificate_number' => $entity->certificate_number,
                'certificate_type'   => $entity->getTypeLabel(),
                'parishioner'        => $entity->parishioner->full_name,
                'issued_date'        => $entity->issued_date->format('F d, Y'),
                'issued_by'          => $entity->issuedBy?->name ?? 'Parish Office',
                'status'             => ucfirst($entity->status),
                'scan_count'         => $qrCode->scan_count,
                'last_scanned'       => $qrCode->last_scanned_at?->diffForHumans(),
            ],
            'Booking' => [
                'type'            => 'Booking',
                'reference'       => $entity->reference_number,
                'service'         => $entity->getTypeLabel(),
                'parishioner'     => $entity->parishioner->full_name,
                'scheduled_date'  => $entity->scheduled_date->format('F d, Y'),
                'scheduled_time'  => $entity->scheduled_time,
                'status'          => $entity->getStatusLabel(),
                'scan_count'      => $qrCode->scan_count,
            ],
            default => [
                'type'   => $type,
                'id'     => $entity->id,
                'status' => 'Verified',
            ],
        };
    }
}
