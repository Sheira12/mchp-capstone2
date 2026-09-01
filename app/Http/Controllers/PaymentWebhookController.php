<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Payment;
use App\Notifications\AdminPaymentNotification;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    /**
     * Handle PayMongo webhook events.
     *
     * GCash/Maya Sources API events:
     *   source.chargeable  — user authorized on PayMongo hosted page; charge the source now
     *   payment.paid       — charge succeeded; set payment to PENDING (awaiting admin verification)
     *   payment.failed     — charge failed; set payment to FAILED
     *
     * ─── IMPORTANT WORKFLOW ────────────────────────────────────────────────────
     * PayMongo confirming payment does NOT automatically mark it PAID.
     * The workflow is:
     *   PayMongo confirms → status = 'pending' (awaiting admin verification)
     *   Admin reviews and clicks "Approve & Mark as Paid" → status = 'paid'
     * ───────────────────────────────────────────────────────────────────────────
     *
     * Webhook URL (register in PayMongo Dashboard → Developers → Webhooks):
     *   POST https://mchp-capstone2.onrender.com/webhooks/paymongo
     *
     * Events to subscribe: source.chargeable, payment.paid, payment.failed
     */
    public function paymongo(Request $request)
    {
        $payload   = $request->getContent();
        $signature = $request->header('Paymongo-Signature');

        Log::info('[PAYMONGO_WEBHOOK_RECEIVED]', [
            'has_signature'  => !empty($signature),
            'content_length' => strlen($payload),
            'ip'             => $request->ip(),
        ]);

        if (!$this->verifySignature($payload, $signature)) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true);

        if (!$event) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] invalid JSON payload');
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $type    = $event['data']['attributes']['type'] ?? null;
        $eventId = $event['data']['id'] ?? null;

        Log::info('[PAYMONGO_WEBHOOK_VERIFIED]', [
            'type'     => $type,
            'event_id' => $eventId,
        ]);

        match ($type) {
            'source.chargeable' => $this->handleSourceChargeable($event),
            'payment.paid'      => $this->handlePaymentPaid($event),
            'payment.failed'    => $this->handlePaymentFailed($event),
            default             => Log::info('[PAYMONGO_WEBHOOK_RECEIVED] unhandled type', ['type' => $type]),
        };

        // Always 200 — PayMongo retries on non-2xx
        return response()->json(['received' => true]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // source.chargeable — user authorized on the hosted checkout page.
    // We must POST to /payments to capture the money.
    // PayMongo will then fire payment.paid (or payment.failed).
    // ─────────────────────────────────────────────────────────────────────────
    private function handleSourceChargeable(array $event): void
    {
        $sourceData = $event['data']['attributes']['data'] ?? [];
        $attributes = $sourceData['attributes'] ?? [];
        $metadata   = $attributes['metadata'] ?? [];
        $sourceId   = $sourceData['id'] ?? null;
        $sourceType = $attributes['type'] ?? 'unknown';

        $referenceNumber = $metadata['reference_number'] ?? null;

        Log::info('[PAYMONGO_WEBHOOK_RECEIVED] source.chargeable', [
            'source_id'        => $sourceId,
            'source_type'      => $sourceType,
            'reference_number' => $referenceNumber,
        ]);

        if (!$sourceId || !$referenceNumber) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] source.chargeable: missing source_id or reference_number', [
                'source_id' => $sourceId,
                'metadata'  => array_keys($metadata),
            ]);
            return;
        }

        $payment = Payment::where('reference_number', $referenceNumber)->first();

        if (!$payment) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] source.chargeable: payment not found', [
                'reference_number' => $referenceNumber,
            ]);
            return;
        }

        // Only charge if still pending — idempotency
        if ($payment->status === 'paid') {
            Log::info('[PAYMONGO_WEBHOOK_RECEIVED] source.chargeable: already paid, skip', [
                'payment_id' => $payment->id,
            ]);
            return;
        }

        try {
            $paymentService = app(PaymentService::class);
            $charged = $paymentService->chargeSource($payment, $sourceId);

            Log::info('[PAYMONGO_WEBHOOK_RECEIVED] source.chargeable: charge sent', [
                'payment_id'  => $payment->id,
                'source_id'   => $sourceId,
                'charge_id'   => $charged['id'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('[PAYMONGO_WEBHOOK_RECEIVED] source.chargeable: charge failed', [
                'payment_id' => $payment->id,
                'source_id'  => $sourceId,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // payment.paid — PayMongo confirmed the charge succeeded.
    //
    // We set status = 'pending' (awaiting admin verification), NOT 'paid'.
    // The admin must click "Approve & Mark as Paid" to finalize.
    // ─────────────────────────────────────────────────────────────────────────
    private function handlePaymentPaid(array $event): void
    {
        $paymentData = $event['data']['attributes']['data'] ?? [];
        $attributes  = $paymentData['attributes'] ?? [];
        $metadata    = $attributes['metadata'] ?? [];
        $paymongoId  = $paymentData['id'] ?? null;

        $referenceNumber = $metadata['reference_number'] ?? null;

        Log::info('[PAYMONGO_WEBHOOK_RECEIVED] payment.paid', [
            'paymongo_payment_id' => $paymongoId,
            'reference_number'    => $referenceNumber,
        ]);

        if (!$referenceNumber) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] payment.paid: no reference_number in metadata', [
                'paymongo_id' => $paymongoId,
                'metadata'    => array_keys($metadata),
            ]);
            return;
        }

        $payment = Payment::where('reference_number', $referenceNumber)->first();

        if (!$payment) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] payment.paid: payment not found', [
                'reference_number' => $referenceNumber,
            ]);
            return;
        }

        // Idempotency: if already pending or paid, skip
        if (in_array($payment->status, ['pending', 'paid'])) {
            Log::info('[PAYMONGO_WEBHOOK_RECEIVED] payment.paid: already in pending/paid state — idempotent skip', [
                'payment_id' => $payment->id,
                'status'     => $payment->status,
            ]);
            return;
        }

        // Record PayMongo's confirmation and store gateway reference.
        // Status stays 'pending' — admin must verify before marking 'paid'.
        DB::transaction(function () use ($payment, $attributes, $paymongoId) {
            $payment->update([
                'status'            => 'pending',   // stays pending until admin approves
                'gateway_reference' => $paymongoId,
                'gateway_response'  => $attributes,
                // Do NOT set paid_at — that is set when admin approves
            ]);
        });

        Log::info('[PAYMENT_PENDING_VERIFICATION]', [
            'payment_id'       => $payment->id,
            'reference_number' => $payment->reference_number,
            'paymongo_id'      => $paymongoId,
            'note'             => 'PayMongo confirmed charge; awaiting admin approval',
        ]);

        // Notify admins: new payment awaiting verification
        $this->notifyAdmins($payment);

        // Audit log
        try {
            AuditLog::record(
                'payment_paymongo_confirmed',
                $payment,
                ['status' => 'pending', 'gateway_reference' => null],
                ['status' => 'pending', 'gateway_reference' => $paymongoId],
                'PayMongo confirmed payment; awaiting admin verification'
            );
        } catch (\Exception $e) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] audit log failed', ['error' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // payment.failed — charge failed.
    // ─────────────────────────────────────────────────────────────────────────
    private function handlePaymentFailed(array $event): void
    {
        $paymentData = $event['data']['attributes']['data'] ?? [];
        $attributes  = $paymentData['attributes'] ?? [];
        $metadata    = $attributes['metadata'] ?? [];

        $referenceNumber = $metadata['reference_number'] ?? null;

        Log::info('[PAYMONGO_WEBHOOK_RECEIVED] payment.failed', [
            'reference_number' => $referenceNumber,
        ]);

        if (!$referenceNumber) return;

        $payment = Payment::where('reference_number', $referenceNumber)->first();

        if (!$payment || $payment->status === 'paid') return; // never override paid

        $payment->update([
            'status'           => 'failed',
            'gateway_response' => $attributes,
        ]);

        Log::info('[PAYMONGO_WEBHOOK_RECEIVED] payment.failed: marked failed', [
            'payment_id'       => $payment->id,
            'reference_number' => $referenceNumber,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Notify all admin users: new payment awaiting verification.
    // Uses Spatie role query — correct for this project.
    // Non-fatal: payment state is never affected by notification failure.
    // ─────────────────────────────────────────────────────────────────────────
    private function notifyAdmins(Payment $payment): void
    {
        try {
            $adminUsers = \App\Models\User::role(['super_admin', 'parish_secretary', 'finance_officer'])
                ->where('is_active', true)
                ->get();

            foreach ($adminUsers as $admin) {
                $admin->notify(new AdminPaymentNotification($payment));
            }

            Log::info('[ADMIN_PAYMENT_NOTIFIED]', [
                'payment_id'  => $payment->id,
                'admin_count' => $adminUsers->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('[ADMIN_PAYMENT_NOTIFIED] notification failed', [
                'payment_id' => $payment->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Signature verification.
    // PayMongo header: Paymongo-Signature: t=TIMESTAMP,te=HMAC_SHA256
    // Signed string: "{timestamp}.{raw_body}"
    // ─────────────────────────────────────────────────────────────────────────
    private function verifySignature(string $payload, ?string $signature): bool
    {
        $secret = config('services.paymongo.webhook_secret');

        if (!$secret
            || $secret === 'RENDER_VAR_OVERRIDE'
            || $secret === 'RAILWAY_VAR_OVERRIDE'
        ) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] signature verification SKIPPED — PAYMONGO_WEBHOOK_SECRET not set');
            return true;
        }

        if (!$signature) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] no Paymongo-Signature header');
            return false;
        }

        $parts = explode(',', $signature);
        $ts = $v1 = null;

        foreach ($parts as $part) {
            $kv = explode('=', $part, 2);
            if (count($kv) !== 2) continue;
            if ($kv[0] === 't')  $ts = $kv[1];
            if ($kv[0] === 'te') $v1 = $kv[1];
        }

        if (!$ts || !$v1) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] malformed signature header');
            return false;
        }

        $expected = hash_hmac('sha256', "{$ts}.{$payload}", $secret);
        $valid    = hash_equals($expected, $v1);

        if (!$valid) {
            Log::warning('[PAYMONGO_WEBHOOK_RECEIVED] HMAC mismatch');
        }

        return $valid;
    }
}
