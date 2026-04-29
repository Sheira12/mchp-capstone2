<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['parishioner', 'booking']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($method = $request->get('method')) {
            $query->where('payment_method', $method);
        }

        if ($from = $request->get('date_from')) {
            $query->where('paid_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->where('paid_at', '<=', $to . ' 23:59:59');
        }

        if ($search = $request->get('search')) {
            $query->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('receipt_number', 'like', "%{$search}%")
                  ->orWhereHas('parishioner', fn($q) => $q->search($search));
        }

        $payments = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Summary stats
        $summary = Payment::paid()
            ->when($request->get('date_from'), fn($q, $v) => $q->where('paid_at', '>=', $v))
            ->when($request->get('date_to'), fn($q, $v) => $q->where('paid_at', '<=', $v . ' 23:59:59'))
            ->select(
                DB::raw('sum(amount) as total'),
                DB::raw('count(*) as count'),
                'payment_method'
            )
            ->groupBy('payment_method')
            ->get();

        return view('admin.payments.index', compact('payments', 'summary'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['parishioner', 'booking', 'certificate', 'refundedBy', 'voidedBy']);
        return view('admin.payments.show', compact('payment'));
    }

    public function recordCash(Request $request)
    {
        $validated = $request->validate([
            'parishioner_id' => ['required', 'exists:parishioners,id'],
            'booking_id'     => ['nullable', 'exists:bookings,id'],
            'certificate_id' => ['nullable', 'exists:certificates,id'],
            'amount'         => ['required', 'numeric', 'min:1'],
            'notes'          => ['nullable', 'string'],
        ]);

        $payment = Payment::create([
            ...$validated,
            'payment_method' => 'cash',
            'status'         => 'paid',
            'paid_at'        => now(),
        ]);

        // Update booking status if linked
        if ($payment->booking) {
            $payment->booking->update(['status' => 'confirmed']);
        }

        AuditLog::record('create', $payment, [], $payment->toArray(), 'Cash payment recorded');

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', 'Cash payment recorded.');
    }

    public function refund(Request $request, Payment $payment)
    {
        $request->validate(['refund_reason' => ['required', 'string']]);

        if ($payment->status !== 'paid') {
            return back()->withErrors(['error' => 'Only paid transactions can be refunded.']);
        }

        $payment->update([
            'status'        => 'refunded',
            'refund_reason' => $request->get('refund_reason'),
            'refunded_by'   => auth()->id(),
            'refunded_at'   => now(),
        ]);

        AuditLog::record('refund', $payment, ['status' => 'paid'], ['status' => 'refunded'], 'Payment refunded');

        return back()->with('success', 'Payment refunded.');
    }

    public function void(Request $request, Payment $payment)
    {
        $request->validate(['void_reason' => ['required', 'string']]);

        if (!in_array($payment->status, ['pending', 'paid'])) {
            return back()->withErrors(['error' => 'This payment cannot be voided.']);
        }

        $payment->update([
            'status'      => 'voided',
            'void_reason' => $request->get('void_reason'),
            'voided_by'   => auth()->id(),
            'voided_at'   => now(),
        ]);

        AuditLog::record('void', $payment, ['status' => $payment->getOriginal('status')], ['status' => 'voided'], 'Payment voided');

        return back()->with('success', 'Payment voided.');
    }

    public function report(Request $request)
    {
        $period = $request->get('period', 'daily');
        $from   = $request->get('date_from', now()->startOfMonth()->toDateString());
        $to     = $request->get('date_to', now()->toDateString());

        $data = Payment::paid()
            ->byDateRange($from, $to . ' 23:59:59')
            ->select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('sum(amount) as total'),
                DB::raw('count(*) as count'),
                'payment_method'
            )
            ->groupBy('date', 'payment_method')
            ->orderBy('date')
            ->get();

        return view('admin.payments.report', compact('data', 'from', 'to', 'period'));
    }
}
