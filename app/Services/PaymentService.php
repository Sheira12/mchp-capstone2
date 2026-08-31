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
     * Create a PayMongo Payment Intent for Credit/Debit Card.
     * Returns a client_key for use with PayMongo.js on the frontend.
     */
    public function createCardPaymentIntent(
        Parishioner $parishioner,
        float $amount,
        string $description,
        ?Booking $booking = null,
        ?Certificate $certificate = null
    ): array {
        // Create payment record first
        $payment = Payment::create([
            'parishioner_id'   => $parishioner->id,
            'booking_id'       => $booking?->id,
            'certificate_id'   => $certificate?->id,
            'amount'           => $amount,
            'payment_method'   => 'card',
            'transaction_type' => 'debit',
            'status'           => 'pending',
        ]);

        try {
            // Step 1: Create Payment Intent
            $response = $this->client->post('/payment_intents', [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'amount'                  => (int) ($amount * 100), // centavos
                            'currency'                => 'PHP',
                            'payment_method_allowed'  => ['card'],
                            'payment_method_options'  => [
                                'card' => ['request_three_d_secure' => 'any'],
                            ],
                            'description'             => $description,
                            'statement_descriptor'    => 'MHC Parish',
                            'metadata'                => [
                                'reference_number' => $payment->reference_number,
                                'parishioner_id'   => $parishioner->id,
                            ],
                        ],
                    ],
                ],
            ]);

            $intentData   = json_decode($response->getBody()->getContents(), true);
            $intentId     = $intentData['data']['id'];
            $clientKey    = $intentData['data']['attributes']['client_key'];

            $payment->update([
                'gateway_reference' => $intentId,
                'gateway_response'  => $intentData['data']['attributes'],
            ]);

            return [
                'success'           => true,
                'payment_intent_id' => $intentId,
                'client_key'        => $clientKey,
                'public_key'        => config('services.paymongo.public_key'),
                'reference_number'  => $payment->reference_number,
                'return_url'        => route('payment.success', ['ref' => $payment->reference_number]),
                'payment'           => $payment,
            ];
        } catch (\Exception $e) {
            Log::error('PayMongo card intent failed', ['error' => $e->getMessage()]);
            $payment->update(['status' => 'failed']);

            return [
                'success' => false,
                'error'   => 'Card payment setup failed. Please try another method.',
                'payment' => $payment,
            ];
        }
    }

    /**
     * Attach a PaymentMethod to a PaymentIntent and confirm it.
     * Called after the frontend creates a PaymentMethod via PayMongo.js.
     */
    public function confirmCardPayment(string $paymentIntentId, string $paymentMethodId): array
    {
        try {
            $response = $this->client->post("/payment_intents/{$paymentIntentId}/attach", [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'payment_method' => $paymentMethodId,
                            'return_url'     => url('/payment/3ds-return'),
                        ],
                    ],
                ],
            ]);

            $data   = json_decode($response->getBody()->getContents(), true);
            $status = $data['data']['attributes']['status'];
            $nextAction = $data['data']['attributes']['next_action'] ?? null;

            return [
                'success'     => true,
                'status'      => $status,
                'next_action' => $nextAction,
                'data'        => $data['data'],
            ];
        } catch (\Exception $e) {
            Log::error('PayMongo card confirm failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a PayMongo payment link for GCash or Maya via the Sources API.
     * The redirect URLs use APP_URL so they work on any deployment.
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
            'parishioner_id'   => $parishioner->id,
            'booking_id'       => $booking?->id,
            'certificate_id'   => $certificate?->id,
            'amount'           => $amount,
            'payment_method'   => $method === 'paymaya' ? 'maya' : 'gcash',
            'transaction_type' => 'debit',
            'status'           => 'pending',
        ]);

        // Build redirect URLs using APP_URL (never localhost in production)
        $appUrl     = rtrim(config('app.url'), '/');
        $successUrl = $appUrl . route('parishioner.payments.success', ['ref' => $payment->reference_number], false);
        $failedUrl  = $appUrl . route('parishioner.payments.failed', [], false) . '?ref=' . $payment->reference_number;

        try {
            // Create PayMongo source (GCash/Maya)
            $response = $this->client->post('/sources', [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'amount'      => (int) round($amount * 100), // centavos, no decimals
                            'currency'    => 'PHP',
                            'type'        => $method,
                            'description' => substr($description, 0, 255),
                            'redirect'    => [
                                'success' => $successUrl,
                                'failed'  => $failedUrl,
                            ],
                            'billing' => [
                                'name'  => $parishioner->full_name,
                                'email' => $parishioner->email ?? 'noreply@mhcparish.ph',
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

            $data        = json_decode($response->getBody()->getContents(), true);
            $checkoutUrl = $data['data']['attributes']['redirect']['checkout_url'] ?? null;

            if (!$checkoutUrl) {
                throw new \RuntimeException('PayMongo did not return a checkout_url. Source status: ' . ($data['data']['attributes']['status'] ?? 'unknown'));
            }

            $payment->update([
                'gateway_reference' => $data['data']['id'],
                'gateway_response'  => $data['data']['attributes'],
            ]);

            Log::info('PayMongo source created', [
                'booking_id'   => $booking?->id,
                'method'       => $method,
                'source_id'    => $data['data']['id'],
                'source_status'=> $data['data']['attributes']['status'] ?? 'unknown',
            ]);

            return [
                'success'      => true,
                'checkout_url' => $checkoutUrl,
                'payment'      => $payment,
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // 4xx from PayMongo — safe to log the response body (no keys in it)
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'no body';
            $statusCode   = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'unknown';

            Log::error('PayMongo source creation failed (client error)', [
                'booking_id'   => $booking?->id,
                'method'       => $method,
                'http_status'  => $statusCode,
                'paymongo_response' => $responseBody,
            ]);

            $payment->update(['status' => 'failed']);
            return [
                'success' => false,
                'error'   => $statusCode === 401
                    ? 'Payment gateway authentication failed. Please contact the parish office.'
                    : 'Payment gateway declined the request. Please try again.',
                'payment' => $payment,
            ];

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('PayMongo connection failed', [
                'booking_id' => $booking?->id,
                'method'     => $method,
                'error'      => $e->getMessage(),
            ]);
            $payment->update(['status' => 'failed']);
            return [
                'success' => false,
                'error'   => 'Unable to connect to the payment gateway. Please check your connection and try again.',
                'payment' => $payment,
            ];

        } catch (\Exception $e) {
            Log::error('PayMongo payment creation failed', [
                'booking_id' => $booking?->id,
                'method'     => $method,
                'error'      => $e->getMessage(),
            ]);
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
