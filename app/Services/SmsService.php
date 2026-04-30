<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $apiKey;
    private string $senderName;
    private string $baseUrl = 'https://api.semaphore.co/api/v4';

    public function __construct()
    {
        $this->apiKey     = config('services.semaphore.api_key', '');
        $this->senderName = config('services.semaphore.sender_name', 'MHCParish');
    }

    /**
     * Send an SMS message to a Philippine mobile number.
     *
     * @param  string $number  e.g. "09171234567" or "+639171234567"
     * @param  string $message
     * @return bool
     */
    public function send(string $number, string $message): bool
    {
        if (empty($this->apiKey) || $this->apiKey === 'your-semaphore-api-key') {
            Log::warning('SmsService: Semaphore API key not configured.');
            return false;
        }

        // Normalize number to 11-digit PH format
        $number = $this->normalizeNumber($number);

        if (!$number) {
            Log::warning('SmsService: Invalid phone number provided.');
            return false;
        }

        try {
            $response = Http::timeout(15)->post("{$this->baseUrl}/messages", [
                'apikey'      => $this->apiKey,
                'number'      => $number,
                'message'     => $message,
                'sendername'  => $this->senderName,
            ]);

            if ($response->successful()) {
                Log::info("SMS sent to {$number}");
                return true;
            }

            Log::error('SmsService: Semaphore API error', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('SmsService: Exception — ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a 2FA OTP via SMS.
     */
    public function sendOtp(string $number, string $code, string $appName = 'MHC Parish'): bool
    {
        $message = "Your {$appName} verification code is: {$code}\n\nDo NOT share this code. Valid for 10 minutes.";
        return $this->send($number, $message);
    }

    /**
     * Normalize Philippine mobile number to 11-digit format (09XXXXXXXXX).
     */
    private function normalizeNumber(string $number): ?string
    {
        // Strip all non-digits
        $digits = preg_replace('/\D/', '', $number);

        // +63XXXXXXXXXX → 09XXXXXXXXXX
        if (strlen($digits) === 12 && str_starts_with($digits, '63')) {
            $digits = '0' . substr($digits, 2);
        }

        // Must be 11 digits starting with 09
        if (strlen($digits) === 11 && str_starts_with($digits, '09')) {
            return $digits;
        }

        return null;
    }

    /**
     * Check if a number looks like a valid PH mobile number.
     */
    public static function isValidPhNumber(string $number): bool
    {
        $digits = preg_replace('/\D/', '', $number);
        if (strlen($digits) === 12 && str_starts_with($digits, '63')) {
            $digits = '0' . substr($digits, 2);
        }
        return strlen($digits) === 11 && str_starts_with($digits, '09');
    }
}
