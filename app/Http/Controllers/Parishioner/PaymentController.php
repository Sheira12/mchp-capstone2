<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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

        $result = $this->paymentService->createPaymentLink(
            parishioner: $parishioner,
            amount:      $validated['amount'],
            description: $booking ? 'Payment for ' . $booking->getTypeLabel() : 'Parish service payment',
            method:      $validated['method'],
            booking:     $booking,
        );

        return response()->json($result);
    }

    public function success(Request $request)
    {
        $ref = $request->get('ref');
        return view('parishioner.payments.success', compact('ref'));
    }

    public function failed(Request $request)
    {
        $ref = $request->get('ref');
        return view('parishioner.payments.failed', compact('ref'));
    }
}
