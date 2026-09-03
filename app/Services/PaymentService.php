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
                            'metadata' => [
                                'reference_number' => (string) $payment->reference_number,
                                'parishioner_id'   => (string) $parishioner->id,
                                'booking_id'       => $booking ? (string) $booking->id : '',
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
     * Create a PayMongo e-wallet payment using the Payment Intent + Payment Method flow.
     *
     * This is the CURRENT PayMongo-supported flow for GCash and Maya (paymaya).
     * The Sources API is no longer supported for Maya and is being deprecated for GCash.
     *
     * Flow:
     *   1. Create Payment Intent (amount, currency, payment_method_allowed)
     *   2. Create Payment Method (type = 'gcash' or 'paymaya', billing)
     *   3. Attach Payment Method to Intent → get redirect URL
     *   4. Redirect customer to the URL
     *   5. Customer authorises in GCash/Maya app/browser
     *   6. Customer returns to return_url
     *   7. PayMongo fires payment.paid webhook
     *
     * @param  string  $method  'gcash' or 'paymaya'
     */
    public function createPaymentLink(
        Parishioner $parishioner,
        float $amount,
        string $description,
        string $method, // 'gcash' or 'paymaya'
        ?Booking $booking = null,
        ?Certificate $certificate = null
    ): array {
        // Normalise: the DB stores 'gcash' or 'maya'
        $dbMethod = $method === 'paymaya' ? 'maya' : 'gcash';

        // Create payment record first so we have a reference number for the URLs
        $payment = Payment::create([
            'parishioner_id'   => $parishioner->id,
            'booking_id'       => $booking?->id,
            'certificate_id'   => $certificate?->id,
            'amount'           => $amount,
            'payment_method'   => $dbMethod,
            'transaction_type' => 'debit',
            'status'           => 'pending',
        ]);

        // Build return URL using APP_URL (never localhost in production)
        $appUrl    = rtrim(config('app.url'), '/');
        $returnUrl = $appUrl . route('parishioner.payments.success', ['ref' => $payment->reference_number], false);
        $failedUrl = $appUrl . route('parishioner.payments.failed', [], false) . '?ref=' . $payment->reference_number;

        $amountCentavos = (int) round($amount * 100);

        Log::info('[PAYMENT_CREATED] e-wallet Payment Intent flow', [
            'payment_id'     => $payment->id,
            'method'         => $method,
            'amount_centavos'=> $amountCentavos,
            'return_url'     => $returnUrl,
        ]);

        try {
            // ── Step 1: Create Payment Intent ──────────────────────────────
            $intentResponse = $this->client->post('/payment_intents', [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'amount'                 => $amountCentavos,
                            'currency'               => 'PHP',
                            'payment_method_allowed' => [$method], // 'gcash' or 'paymaya'
                            'description'            => substr($description, 0, 255),
                            'metadata'               => [
                                'reference_number' => (string) $payment->reference_number,
                                'parishioner_id'   => (string) $parishioner->id,
                                'booking_id'       => $booking ? (string) $booking->id : '',
                            ],
                        ],
                    ],
                ],
            ]);

            $intentData = json_decode($intentResponse->getBody()->getContents(), true);
            $intentId   = $intentData['data']['id'] ?? null;

            if (!$intentId) {
                throw new \RuntimeException('PayMongo did not return a payment_intent id.');
            }

            Log::info('[PAYMONGO_REDIRECT] Payment Intent created', [
                'payment_id' => $payment->id,
                'intent_id'  => $intentId,
                'method'     => $method,
            ]);

            // ── Step 2: Create Payment Method ──────────────────────────────
            $methodResponse = $this->client->post('/payment_methods', [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'type'    => $method, // 'gcash' or 'paymaya'
                            'billing' => [
                                'name'  => $parishioner->full_name,
                                'email' => $parishioner->email ?? 'noreply@mhcparish.ph',
                                'phone' => $parishioner->contact_number ?? '',
                            ],
                        ],
                    ],
                ],
            ]);

            $methodData = json_decode($methodResponse->getBody()->getContents(), true);
            $methodId   = $methodData['data']['id'] ?? null;

            if (!$methodId) {
                throw new \RuntimeException('PayMongo did not return a payment_method id.');
            }

            // ── Step 3: Attach Payment Method to Intent ─────────────────────
            $attachResponse = $this->client->post("/payment_intents/{$intentId}/attach", [
                'json' => [
                    'data' => [
                        'attributes' => [
                            'payment_method' => $methodId,
                            'return_url'     => $returnUrl,
                        ],
                    ],
                ],
            ]);

            $attachData  = json_decode($attachResponse->getBody()->getContents(), true);
            $intentStatus = $attachData['data']['attributes']['status'] ?? null;
            $redirectUrl  = $attachData['data']['attributes']['next_action']['redirect']['url'] ?? null;

            Log::info('[PAYMONGO_REDIRECT] Payment Method attached', [
                'payment_id'    => $payment->id,
                'intent_id'     => $intentId,
                'method_id'     => $methodId,
                'intent_status' => $intentStatus,
                'has_redirect'  => !empty($redirectUrl),
            ]);

            if (!$redirectUrl) {
                // Intent may have succeeded immediately (unlikely for e-wallets, but handle it)
                if ($intentStatus === 'succeeded') {
                    $payment->update([
                        'gateway_reference' => $intentId,
                        'status'            => 'pending', // still needs admin verification
                    ]);
                    return [
                        'success'      => true,
                        'checkout_url' => $returnUrl, // send straight to success page
                        'payment'      => $payment,
                    ];
                }
                throw new \RuntimeException(
                    'PayMongo did not return a redirect URL. Intent status: ' . ($intentStatus ?? 'unknown')
                );
            }

            // Store the intent ID as gateway reference (pay_intent_...)
            $payment->update([
                'gateway_reference' => $intentId,
                'gateway_response'  => $attachData['data']['attributes'],
            ]);

            return [
                'success'      => true,
                'checkout_url' => $redirectUrl,
                'payment'      => $payment,
            ];

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()
                ? $e->getResponse()->getBody()->getContents()
                : 'no response body';
            $statusCode = $e->getResponse()
                ? $e->getResponse()->getStatusCode()
                : 'unknown';

            // Parse PayMongo's error detail for a user-friendly message
            $errorData    = json_decode($responseBody, true);
            $pmError      = $errorData['errors'][0] ?? [];
            $pmCode       = $pmError['code'] ?? 'unknown_error';
            $pmDetail     = $pmError['detail'] ?? 'No detail provided.';

            Log::error('[PAYMONGO_REDIRECT] e-wallet Payment Intent failed (client error)', [
                'payment_id'        => $payment->id,
                'method'            => $method,
                'http_status'       => $statusCode,
                'paymongo_code'     => $pmCode,
                'paymongo_detail'   => $pmDetail,
                'amount_centavos'   => $amountCentavos,
            ]);

            $payment->update(['status' => 'failed']);

            $userMessage = match (true) {
                $statusCode === 401
                    => 'Payment gateway authentication failed. Please contact the parish office.',
                str_contains($pmCode, 'below_minimum')
                    => 'Payment amount is below the minimum allowed by ' . strtoupper($dbMethod) . '.',
                str_contains($pmCode, 'above_maximum')
                    => 'Payment amount exceeds the maximum allowed by ' . strtoupper($dbMethod) . '.',
                str_contains($pmDetail, 'not allowed')
                    => strtoupper($dbMethod) . ' payments are not enabled on this account. Please use a different payment method.',
                default
                    => 'Unable to create ' . strtoupper($dbMethod) . ' checkout. Please try again.',
            };

            return [
                'success' => false,
                'error'   => $userMessage,
                'payment' => $payment,
            ];

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('[PAYMONGO_REDIRECT] connection failed', [
                'payment_id' => $payment->id,
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
            Log::error('[PAYMONGO_REDIRECT] unexpected error', [
                'payment_id' => $payment->id,
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
     * Poll a PayMongo Payment Intent status directly.
     * Used by PaymentController::checkStatus() as a fallback when webhook is delayed.
     *
     * Possible intent statuses: awaiting_payment_method, awaiting_next_action,
     *                           processing, succeeded, awaiting_capture
     */
    public function getPaymentIntentStatus(string $intentId): array
    {
        $response = $this->client->get("/payment_intents/{$intentId}");
        $data     = json_decode($response->getBody()->getContents(), true);

        return [
            'id'     => $data['data']['id'] ?? $intentId,
            'status' => $data['data']['attributes']['status'] ?? 'unknown',
            'amount' => $data['data']['attributes']['amount'] ?? 0,
        ];
    }

    /**
     * Charge a chargeable PayMongo Source (GCash/Maya).
     *
     * Called from:
     *   - PaymentWebhookController::handleSourceChargeable() — via webhook
     *   - PaymentController::checkStatus() — as a client-side fallback
     *
     * Returns the PayMongo payment object data on success, or throws on failure.
     * The caller is responsible for catching exceptions.
     */
    public function chargeSource(Payment $payment, string $sourceId): array
    {
        $response = $this->client->post('/payments', [
            'json' => [
                'data' => [
                    'attributes' => [
                        'amount'      => (int) round($payment->amount * 100),
                        'currency'    => 'PHP',
                        'description' => 'Parish payment - ' . $payment->reference_number,
                        'source'      => [
                            'id'   => $sourceId,
                            'type' => 'source',
                        ],
                        'metadata' => [
                            'reference_number' => (string) $payment->reference_number,
                            'parishioner_id'   => (string) $payment->parishioner_id,
                            'booking_id'       => $payment->booking_id ? (string) $payment->booking_id : '',
                        ],
                    ],
                ],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        Log::info('PaymentService: source charged', [
            'payment_id'         => $payment->id,
            'source_id'          => $sourceId,
            'paymongo_charge_id' => $data['data']['id'] ?? null,
            'charge_status'      => $data['data']['attributes']['status'] ?? 'unknown',
        ]);

        return $data['data'] ?? [];
    }

    /**
     * Poll a PayMongo Source by its source_id and return its current status.
     * Used by PaymentController::checkStatus() as a webhook fallback.
     *
     * Possible source statuses: pending, chargeable, consumed, cancelled, expired
     */
    public function getSourceStatus(string $sourceId): array
    {
        $response = $this->client->get("/sources/{$sourceId}");
        $data     = json_decode($response->getBody()->getContents(), true);

        return [
            'id'     => $data['data']['id'] ?? $sourceId,
            'status' => $data['data']['attributes']['status'] ?? 'unknown',
            'amount' => $data['data']['attributes']['amount'] ?? 0,
            'type'   => $data['data']['attributes']['type'] ?? 'unknown',
        ];
    }
}
