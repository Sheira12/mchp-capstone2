<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Parishioner;
use App\Models\Payment;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private Client $client;
    private string $baseUrl = 'https://api.paymongo.com/v1';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers'  => [
                'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);
    }

    /**
     * Create a PayMongo payment link for GCash or Maya.
     */
    public function createPaymentLink(
        Parishioner $parishioner,
        float $amount,
        string $description,
        string $method, // 'gcash' or 'paymaya'
        ?Booking $booking = null,
        ?Certificate $certificate = null
    ): array {
        // Create payment record first
        $payment = Payment::create([
            'parishioner_id' => $parishioner->id,
            'booking_id'     => $booking?->id,
            'certificate_id' => $certificate?->id,
            'amount'         => $amount,
            'payment_method' => $method === 'paymaya' ? 'maya' : 'gcash',
            'status'         => 'pending',
        ]);

        try {
            // Create PayMongo source
            $response = $this->client->post('/sources', [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'amount'      => (int) ($amount * 100), // in centavos
                            'currency'    => 'PHP',
                            'type'        => $method,
                            'description' => $description,
                            'redirect'    => [
                                'success' => route('payment.success', ['ref' => $payment->reference_number]),
                                'failed'  => route('payment.failed', ['ref' => $payment->reference_number]),
                            ],
                            'billing' => [
                                'name'  => $parishioner->full_name,
                                'email' => $parishioner->email ?? '',
                                'phone' => $parishioner->contact_number ?? '',
                            ],
                            'metadata' => [
                                'reference_number' => $payment->reference_number,
                                'parishioner_id'   => $parishioner->id,
                            ],
                        ],
                    ],
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $checkoutUrl = $data['data']['attributes']['redirect']['checkout_url'];

            $payment->update([
                'gateway_reference' => $data['data']['id'],
                'gateway_response'  => $data['data']['attributes'],
            ]);

            return [
                'success'      => true,
                'checkout_url' => $checkoutUrl,
                'payment'      => $payment,
            ];
        } catch (\Exception $e) {
            Log::error('PayMongo payment creation failed', ['error' => $e->getMessage()]);
            $payment->update(['status' => 'failed']);

            return [
                'success' => false,
                'error'   => 'Payment gateway error. Please try again or contact the parish office.',
                'payment' => $payment,
            ];
        }
    }

    /**
     * Check payment status from PayMongo.
     */
    public function checkStatus(Payment $payment): string
    {
        if (!$payment->gateway_reference) return $payment->status;

        try {
            $response = $this->client->get("/sources/{$payment->gateway_reference}");
            $data     = json_decode($response->getBody()->getContents(), true);
            $status   = $data['data']['attributes']['status'] ?? null;

            if ($status === 'chargeable') {
                // Charge the source
                $this->chargeSource($payment, $data['data']['id']);
            }

            return $status ?? $payment->status;
        } catch (\Exception $e) {
            Log::error('PayMongo status check failed', ['error' => $e->getMessage()]);
            return $payment->status;
        }
    }

    private function chargeSource(Payment $payment, string $sourceId): void
    {
        try {
            $this->client->post('/payments', [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'amount'      => (int) ($payment->amount * 100),
                            'currency'    => 'PHP',
                            'description' => "Parish payment - {$payment->reference_number}",
                            'source'      => [
                                'id'   => $sourceId,
                                'type' => 'source',
                            ],
                            'metadata' => [
                                'reference_number' => $payment->reference_number,
                            ],
                        ],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('PayMongo charge failed', ['error' => $e->getMessage()]);
        }
    }
}
