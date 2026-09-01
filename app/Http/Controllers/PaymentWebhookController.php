<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Payment;
use App\Notifications\PaymentReceiptNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle PayMongo webhook events.
     *
     * PayMongo sends webhooks for:
     *   payment.paid   — GCash/Maya source was charged and paid
     *   payment.failed — source charge failed
     *
     * Webhook URL: POST /webhooks/paymongo
     * Must be registered in PayMongo Dashboard → Developers → Webhooks
     * Must be HTTPS and publicly accessible (no auth middleware)
     */
    public function paymongo(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('Paymongo-Signature');

        Log::info('PayMongo webhook received', [
            'has_signature' => !empty($signature),
            'content_length' => strlen($payload),
        ]);

        // Verify signature (skip if webhook secret not configured — dev convenience only)
        if (!$this->verifySignature($payload, $signature)) {
            Log::warning('PayMongo webhook: signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true);

        if (!$event) {
            Log::warning('PayMongo webhook: invalid JSON payload');
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $type    = $event['data']['attributes']['type'] ?? null;
        $eventId = $event['data']['id'] ?? null;

        Log::info('PayMongo webhook processing', [
            'type'     => $type,
            'event_id' => $eventId,
        ]);

        // Always return 200 fast — process the event
        match ($type) {
            'payment.paid'   => $this->handlePaymentPaid($event),
            'payment.failed' => $this->handlePaymentFailed($event),
            default          => Log::info('PayMongo webhook: unhandled event type', ['type' => $type]),
        };

        return response()->json(['received' => true]);
    }

    private function handlePaymentPaid(array $event): void
    {
        // PayMongo payment.paid event structure:
        // event.data.attributes.data = the payment object
        // event.data.attributes.data.attributes.metadata = our metadata
        $paymentData = $event['data']['attributes']['data'] ?? [];
        $attributes  = $paymentData['attributes'] ?? [];
        $metadata    = $attributes['metadata'] ?? [];
        $paymongoId  = $paymentData['id'] ?? null;

        $referenceNumber = $metadata['reference_number'] ?? null;

        Log::info('PayMongo payment.paid', [
            'paymongo_payment_id' => $paymongoId,
            'reference_number'    => $referenceNumber,
            'status'              => $attributes['status'] ?? 'unknown',
        ]);

        if (!$referenceNumber) {
            Log::warning('PayMongo webhook: no reference_number in metadata', [
                'metadata' => array_keys($metadata),
            ]);
            return;
        }

        $payment = Payment::where('reference_number', $referenceNumber)->first();

        if (!$payment) {
            Log::warning('PayMongo webhook: payment not found', [
                'reference_number' => $referenceNumber,
            ]);
            return;
        }

        // ── Idempotency: skip if already paid ───────────────────────────────
        if ($payment->status === 'paid') {
            Log::info('PayMongo webhook: payment already marked paid (idempotent skip)', [
                'payment_id'      => $payment->id,
                'reference_number'=> $referenceNumber,
            ]);
            return;
        }

        // ── Mark payment as PAID atomically ──────────────────────────────────
        DB::transaction(function () use ($payment, $event, $paymentData, $attributes, $paymongoId) {
            $payment->update([
                'status'            => 'paid',
                'gateway_reference' => $paymongoId,
                'gateway_response'  => $attributes,
                'paid_at'           => now(),
            ]);

            // Update linked booking to confirmed
            if ($payment->booking) {
                $payment->booking->update(['status' => 'confirmed']);
                Log::info('PayMongo webhook: booking confirmed', [
                    'booking_id' => $payment->booking->id,
                ]);
            }
        });

        Log::info('PayMongo webhook: payment marked paid', [
            'payment_id'       => $payment->id,
            'reference_number' => $payment->reference_number,
            'paymongo_id'      => $paymongoId,
        ]);

        // ── Send receipt notification (non-fatal) ────────────────────────────
        try {
            $notifiable = $payment->parishioner?->user ?? null;
            if ($notifiable?->email) {
                $notifiable->notify(new PaymentReceiptNotification($payment));
                Log::info('PayMongo webhook: receipt notification sent', [
                    'payment_id' => $payment->id,
                ]);
            } else {
                Log::info('PayMongo webhook: no email for receipt notification', [
                    'payment_id' => $payment->id,
                ]);
            }
        } catch (\Exception $e) {
            // Email failure MUST NOT affect payment status
            Log::error('PayMongo webhook: receipt notification failed (payment still paid)', [
                'payment_id' => $payment->id,
                'error'      => $e->getMessage(),
            ]);
        }

        try {
            AuditLog::record(
                'payment_paid',
                $payment,
                ['status' => 'pending'],
                ['status' => 'paid'],
                'Payment confirmed via PayMongo webhook'
            );
        } catch (\Exception $e) {
            Log::warning('PayMongo webhook: audit log failed', ['error' => $e->getMessage()]);
        }
    }

    private function handlePaymentFailed(array $event): void
    {
        $paymentData = $event['data']['attributes']['data'] ?? [];
        $attributes  = $paymentData['attributes'] ?? [];
        $metadata    = $attributes['metadata'] ?? [];

        $referenceNumber = $metadata['reference_number'] ?? null;

        Log::info('PayMongo payment.failed', [
            'reference_number' => $referenceNumber,
        ]);

        if (!$referenceNumber) return;

        $payment = Payment::where('reference_number', $referenceNumber)->first();

        if (!$payment || $payment->status === 'paid') return; // Don't override a paid status

        $payment->update([
            'status'           => 'failed',
            'gateway_response' => $attributes,
        ]);

        Log::info('PayMongo webhook: payment marked failed', [
            'payment_id'       => $payment->id,
            'reference_number' => $referenceNumber,
        ]);
    }

    /**
     * Verify PayMongo webhook signature.
     *
     * PayMongo sends: Paymongo-Signature: t=TIMESTAMP,te=HMAC_SHA256
     * Signed payload: "{timestamp}.{raw_body}"
     * Algorithm: HMAC-SHA256 with webhook signing secret
     */
    private function verifySignature(string $payload, ?string $signature): bool
    {
        $secret = config('services.paymongo.webhook_secret');

        // If no secret configured, skip verification (dev/local convenience)
        // In production, PAYMONGO_WEBHOOK_SECRET must be set
        if (!$secret || $secret === 'RENDER_VAR_OVERRIDE' || $secret === 'RAILWAY_VAR_OVERRIDE') {
            Log::warning('PayMongo webhook: signature verification SKIPPED (no webhook secret configured)');
            return true;
        }

        if (!$signature) {
            Log::warning('PayMongo webhook: no Paymongo-Signature header');
            return false;
        }

        $parts = explode(',', $signature);
        $ts    = null;
        $v1    = null;

        foreach ($parts as $part) {
            $kv = explode('=', $part, 2);
            if (count($kv) !== 2) continue;
            if ($kv[0] === 't')  $ts = $kv[1];
            if ($kv[0] === 'te') $v1 = $kv[1];
        }

        if (!$ts || !$v1) {
            Log::warning('PayMongo webhook: malformed signature header', ['signature' => substr($signature, 0, 50)]);
            return false;
        }

        $signedPayload = "{$ts}.{$payload}";
        $expected      = hash_hmac('sha256', $signedPayload, $secret);

        $valid = hash_equals($expected, $v1);

        if (!$valid) {
            Log::warning('PayMongo webhook: HMAC mismatch');
        }

        return $valid;
    }
}
