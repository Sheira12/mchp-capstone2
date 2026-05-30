<?php

namespace App\Console\Commands;

use App\Models\QrCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class FixQrCodes extends Command
{
    protected $signature   = 'parish:fix-qrcodes';
    protected $description = 'Update all QR verification URLs to match APP_URL and regenerate QR images';

    public function handle(): void
    {
        $appUrl = config('app.url');
        $qrCodes = QrCode::all();

        if ($qrCodes->isEmpty()) {
            $this->info('No QR codes found.');
            return;
        }

        $this->info("Fixing {$qrCodes->count()} QR code(s) — APP_URL: {$appUrl}");

        foreach ($qrCodes as $qr) {
            // Fix the verification URL
            $correctUrl = $appUrl . '/verify/' . $qr->token;

            // Determine image path based on entity type
            $type = class_basename($qr->qr_codeable_type);
            $path = "qrcodes/{$type}/{$qr->token}.svg";

            // Regenerate SVG image
            try {
                $svg = QrCodeGenerator::format('svg')
                    ->size(200)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($correctUrl);

                Storage::disk('public')->put($path, $svg);

                $qr->update([
                    'verification_url' => $correctUrl,
                    'qr_image_path'    => $path,
                ]);

                $this->line("  Fixed: {$qr->token} → {$correctUrl}");
            } catch (\Exception $e) {
                $this->error("  Failed for token {$qr->token}: " . $e->getMessage());
            }
        }

        // Also reset certificate file_path so PDFs regenerate with correct QR
        $certCount = \DB::table('certificates')->update(['file_path' => null]);
        $this->info("Reset {$certCount} certificate PDF(s) — they will regenerate on next download.");

        $this->info('Done! All QR codes updated.');
    }
}
