<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Payment;
use App\Notifications\PaymentReceiptNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle PayMongo webhook (covers GCash and Maya via PayMongo gateway).
     */
    public function paymongo(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('Paymongo-Signature');

        if (!$this->verifySignature($payload, $signature)) {
            Log::warning('PayMongo webhook signature mismatch');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true);
        $type  = $event['data']['attributes']['type'] ?? null;

        Log::info('PayMongo webhook received', ['type' => $type]);

        match ($type) {
            'payment.paid'   => $this->handlePaymentPaid($event),
            'payment.failed' => $this->handlePaymentFailed($event),
            default          => null,
        };

        return response()->json(['received' => true]);
    }

    private function handlePaymentPaid(array $event): void
    {
        $attributes = $event['data']['attributes']['data']['attributes'] ?? [];
        $metadata   = $attributes['metadata'] ?? [];
        $referenceNumber = $metadata['reference_number'] ?? null;

        if (!$referenceNumber) {
            Log::warning('PayMongo webhook: no reference_number in metadata');
            return;
        }

        $payment = Payment::where('reference_number', $referenceNumber)->first();

        if (!$payment) {
            Log::warning("PayMongo webhook: payment not found for ref {$referenceNumber}");
            return;
        }

        $payment->update([
            'status'            => 'paid',
            'gateway_reference' => $event['data']['id'],
            'gateway_response'  => $attributes,
            'paid_at'           => now(),
        ]);

        // Update linked booking
        if ($payment->booking) {
            $payment->booking->update(['status' => 'confirmed']);
        }

        // Send receipt
        if ($payment->parishioner?->email) {
            $payment->parishioner->notify(new PaymentReceiptNotification($payment));
        }

        AuditLog::record('payment_paid', $payment, ['status' => 'pending'], ['status' => 'paid'], 'Payment confirmed via webhook');
    }

    private function handlePaymentFailed(array $event): void
    {
        $attributes      = $event['data']['attributes']['data']['attributes'] ?? [];
        $metadata        = $attributes['metadata'] ?? [];
        $referenceNumber = $metadata['reference_number'] ?? null;

        if (!$referenceNumber) return;

        $payment = Payment::where('reference_number', $referenceNumber)->first();
        $payment?->update([
            'status'           => 'failed',
            'gateway_response' => $attributes,
        ]);
    }

    private function verifySignature(string $payload, ?string $signature): bool
    {
        if (!$signature) return false;

        $secret = config('services.paymongo.webhook_secret');
        if (!$secret) return true; // Skip in dev if not configured

        // PayMongo uses timestamp.payload HMAC-SHA256
        $parts = explode(',', $signature);
        $ts    = null;
        $v1    = null;

        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part, 2);
            if ($key === 't') $ts = $value;
            if ($key === 'te') $v1 = $value;
        }

        if (!$ts || !$v1) return false;

        $signedPayload = "{$ts}.{$payload}";
        $expected      = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $v1);
    }
}
