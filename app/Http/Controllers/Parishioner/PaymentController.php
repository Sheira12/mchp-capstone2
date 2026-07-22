<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\PaymentReceiptNotification;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index()
    {
        $parishioner = auth()->user()->parishioner;
        $payments    = $parishioner?->payments()->with('booking')->orderByDesc('created_at')->paginate(10) ?? collect();

        return view('parishioner.payments.index', compact('payments'));
    }

    /**
     * Initiate an online payment (GCash or Maya).
     * Uses PayMongo if properly configured, otherwise uses simulated demo mode.
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'method'     => ['required', 'in:gcash,paymaya'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'amount'     => ['required', 'numeric', 'min:1'],
        ]);

        $parishioner = auth()->user()->parishioner;

        if (!$parishioner) {
            return response()->json(['success' => false, 'error' => 'Profile not found.'], 422);
        }

        $booking = $validated['booking_id'] ? Booking::find($validated['booking_id']) : null;

        if ($booking && $booking->parishioner_id !== $parishioner->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized.'], 403);
        }

        $secretKey = config('services.paymongo.secret_key');
        $isConfigured = $secretKey
            && !str_contains($secretKey, 'xxxxxxxxxxxx')
            && !str_contains($secretKey, 'PASTE_YOUR');

        if ($isConfigured) {
            // Try PayMongo
            try {
                $result = $this->paymentService->createPaymentLink(
                    parishioner: $parishioner,
                    amount:      $validated['amount'],
                    description: $booking ? 'Payment for ' . $booking->getTypeLabel() : 'Parish service payment',
                    method:      $validated['method'],
                    booking:     $booking,
                );
                return response()->json($result);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('PayMongo failed: ' . $e->getMessage());
                // Fall through to QR method
            }
        }

        // PayMongo not available — use QR/manual method
        return response()->json([
            'success' => false,
            'use_qr'  => true,
            'error'   => 'Please use the QR code method below to complete your payment.',
        ], 422);
    }

    /**
     * Show the payment selection page for a booking.
     */
    public function payBooking(Booking $booking)
    {
        // Ensure booking belongs to this parishioner
        if ($booking->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }

        if ($booking->payment?->status === 'paid') {
            return redirect()->route('parishioner.payments.receipt', $booking->payment)
                ->with('info', 'This booking has already been paid.');
        }

        return view('parishioner.payments.pay', compact('booking'));
    }

    /**
     * Process a cash payment request (parishioner declares intent to pay cash).
     * Admin will confirm receipt at the office.
     */
    public function payCash(Request $request, Booking $booking)
    {
        if ($booking->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }

        if ($booking->payment?->status === 'paid') {
            return back()->with('error', 'This booking has already been paid.');
        }

        // Create a pending cash payment — admin will mark as paid when cash is received
        $payment = Payment::create([
            'parishioner_id' => $booking->parishioner_id,
            'booking_id'     => $booking->id,
            'amount'         => $booking->service_fee,
            'payment_method' => 'cash',
            'status'         => 'pending',
            'payer_contact'  => $booking->parishioner->contact_number,
            'notes'          => 'Cash payment — to be collected at parish office.',
        ]);

        return redirect()->route('parishioner.bookings.show', $booking)
            ->with('success', 'Cash payment request recorded. Please bring ₱' . number_format($booking->service_fee, 2) . ' to the parish office to complete your payment.');
    }

    /**
     * Submit proof of payment (reference number + optional screenshot).
     * Requires OTP verification before submission.
     */
    public function submitProof(Request $request, Booking $booking)
    {
        if ($booking->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_method'       => ['required', 'in:gcash,maya'],
            'submitted_reference'  => ['required', 'string', 'max:100'],
            'payer_contact'        => ['nullable', 'string', 'max:20'],
            'proof'                => ['nullable', 'image', 'max:5120'], // 5MB max
            'otp_code'             => ['required', 'string', 'size:6'],
        ]);

        // Verify OTP before processing payment
        $user = auth()->user();
        if (!$user->validateTwoFactorCode($validated['otp_code'])) {
            return back()
                ->withInput()
                ->withErrors(['otp_code' => 'Invalid or expired verification code. Please request a new code.']);
        }
        $user->clearTwoFactorCode();

        // Handle proof upload
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payments/proofs', 'public');
        }

        // Create or update payment record
        $payment = $booking->payment ?? new Payment();
        $payment->fill([
            'parishioner_id'      => $booking->parishioner_id,
            'booking_id'          => $booking->id,
            'amount'              => $booking->service_fee,
            'payment_method'      => $validated['payment_method'],
            'status'              => 'pending', // Admin must verify
            'submitted_reference' => $validated['submitted_reference'],
            'payer_contact'       => $validated['payer_contact'] ?? $booking->parishioner->contact_number,
            'notes'               => 'Payment submitted via ' . strtoupper($validated['payment_method']) . '. OTP verified. Awaiting admin verification.',
        ]);

        if ($proofPath) {
            $payment->proof_path = $proofPath;
        }

        $payment->save();

        return redirect()->route('parishioner.bookings.show', $booking)
            ->with('success', 'Payment submitted and OTP verified! Our team will confirm your payment within 24 hours.');
    }

    /**
     * Send OTP for payment verification.
     */
    public function sendPaymentOtp(Request $request)
    {
        $user = auth()->user();
        $code = $user->generateTwoFactorCode();

        $sent = false;
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\TwoFactorCodeMail($user, $code));
            $sent = true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Payment OTP email failed: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'sent'    => $sent,
                'message' => $sent
                    ? 'Verification code sent to ' . \Illuminate\Support\Str::mask($user->email, '*', 2, -strlen(explode('@', $user->email)[1]) - 3)
                    : 'Email unavailable. Code: ' . $code,
                'dev_code' => !$sent ? $code : null,
            ]);
        }

        return back()->with('otp_sent', true);
    }

    /**
     * Show a simulated GCash/Maya checkout page for capstone demo.
     */
    public function demoCheckout(Booking $booking, string $method)
    {
        if ($booking->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }

        if (!in_array($method, ['gcash', 'maya'])) {
            abort(404);
        }

        return view('parishioner.payments.demo-checkout', compact('booking', 'method'));
    }

    /**
     * Complete the simulated payment — marks as paid instantly for demo.
     */
    public function demoComplete(Request $request, Booking $booking, string $method)
    {
        if ($booking->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }

        $request->validate([
            'demo_reference' => ['required', 'string'],
        ]);

        // Create a paid payment record (simulated)
        $payment = Payment::create([
            'parishioner_id'      => $booking->parishioner_id,
            'booking_id'          => $booking->id,
            'amount'              => $booking->service_fee,
            'payment_method'      => $method === 'gcash' ? 'gcash' : 'maya',
            'status'              => 'paid',
            'submitted_reference' => $request->get('demo_reference'),
            'payer_contact'       => $booking->parishioner->contact_number,
            'paid_at'             => now(),
            'notes'               => 'Demo payment — simulated for capstone presentation.',
            'verified_by'         => null,
            'verified_at'         => now(),
        ]);

        // Update booking to confirmed
        if ($booking->status === 'pending') {
            $booking->update(['status' => 'confirmed']);
        }

        // Send receipt notification
        $linkedUser = \App\Models\User::where('parishioner_id', $booking->parishioner_id)->first();
        if ($linkedUser) {
            try {
                $linkedUser->notify(new \App\Notifications\PaymentReceiptNotification($payment));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Receipt notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('parishioner.payments.receipt', $payment)
            ->with('success', 'Payment completed successfully!');
    }
    public function receipt(Payment $payment)
    {
        // Ensure payment belongs to this parishioner
        if ($payment->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }

        $payment->load(['booking', 'parishioner']);

        return view('parishioner.payments.receipt', compact('payment'));
    }

    /**
     * Download official receipt as PDF.
     */
    public function receiptPdf(Payment $payment)
    {
        if ($payment->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }

        $payment->load(['booking', 'parishioner']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('parishioner.payments.receipt-pdf', [
            'payment'  => $payment,
            'parish'   => [
                'name'    => config('parish.name'),
                'address' => config('parish.address'),
                'phone'   => config('parish.phone'),
                'email'   => config('parish.email'),
                'priest'  => config('parish.priest'),
            ],
            'logoPath' => public_path('images/parish-logo.png'),
        ])->setPaper('A4', 'portrait');

        return $pdf->download('OR-' . $payment->receipt_number . '.pdf');
    }

    /**
     * PayMongo redirect — payment success.
     */
    public function success(Request $request)
    {
        $ref     = $request->get('ref');
        $payment = $ref ? Payment::where('reference_number', $ref)->first() : null;

        // If payment is found and paid, show receipt
        if ($payment && $payment->status === 'paid') {
            return redirect()->route('parishioner.payments.receipt', $payment);
        }

        return view('parishioner.payments.success', compact('ref', 'payment'));
    }

    /**
     * PayMongo redirect — payment failed.
     */
    public function failed(Request $request)
    {
        $ref = $request->get('ref');
        return view('parishioner.payments.failed', compact('ref'));
    }
}
