<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Show the Order of Payment creation page for a booking.
     * Parishioner selects a package (or standard) and confirms the amount.
     */
    public function createForBooking(Booking $booking)
    {
        $this->authorize('view', $booking);

        // If an order already exists and is pending, redirect to it
        if ($booking->order && $booking->order->status === 'pending' && !$booking->order->isExpired()) {
            return redirect()->route('parishioner.orders.show', $booking->order);
        }

        $service  = Service::where('slug', $booking->booking_type)->first();
        $packages = $service ? $service->packages()->get() : collect();

        return view('parishioner.orders.create', compact('booking', 'service', 'packages'));
    }

    /**
     * Generate the Order of Payment.
     * Amount is taken entirely from the package/service — never from user input.
     */
    public function storeForBooking(Request $request, Booking $booking)
    {
        $this->authorize('view', $booking);

        $validated = $request->validate([
            'service_package_id' => ['nullable', 'exists:service_packages,id'],
            'notes'              => ['nullable', 'string', 'max:500'],
        ]);

        $service = Service::where('slug', $booking->booking_type)->first();

        // Resolve package and price
        $package    = null;
        $lineItems  = [];
        $total      = 0;

        if (!empty($validated['service_package_id'])) {
            $package = ServicePackage::find($validated['service_package_id']);
            // Security: package must belong to this service
            if (!$package || $package->service_id !== $service?->id) {
                return back()->withErrors(['service_package_id' => 'Invalid package selected.']);
            }
            // Build line items from package
            $lineItems[] = ['label' => $package->name . ' Package', 'amount' => (float) $package->price];
            foreach ($package->inclusionsList() as $inc) {
                $lineItems[] = ['label' => '  • ' . $inc, 'amount' => 0]; // descriptive rows
            }
            $total = (float) $package->price;
        } else {
            // Standard / no package — use base service fee
            $fee         = (float) ($service?->fee ?? $booking->service_fee ?? 0);
            $lineItems[] = ['label' => ($service?->name ?? $booking->getTypeLabel()) . ' — Standard Fee', 'amount' => $fee];
            $total       = $fee;
        }

        // Create the Order
        $order = Order::create([
            'parishioner_id'     => $booking->parishioner_id,
            'booking_id'         => $booking->id,
            'service_id'         => $service?->id,
            'service_package_id' => $package?->id,
            'line_items'         => $lineItems,
            'subtotal'           => $total,
            'fees'               => 0,
            'total'              => $total,
            'status'             => 'pending',
            'notes'              => $validated['notes'] ?? null,
            'created_by'         => auth()->id(),
        ]);

        // Link booking to order and update service fee from order total
        $booking->update([
            'order_id'           => $order->id,
            'service_package_id' => $package?->id,
            'service_fee'        => $total,
        ]);

        return redirect()->route('parishioner.orders.show', $order)
            ->with('success', 'Order of Payment generated. Please review and proceed to payment.');
    }

    /**
     * Show the Order of Payment detail page.
     */
    public function show(Order $order)
    {
        if ($order->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }
        $order->load(['booking', 'package', 'service', 'payment']);

        // Auto-expire
        if ($order->isExpired()) {
            $order->update(['status' => 'expired']);
        }

        return view('parishioner.orders.show', compact('order'));
    }

    /**
     * Download the Order of Payment as PDF.
     */
    public function pdf(Order $order)
    {
        if ($order->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }
        $order->load(['booking.parishioner', 'package', 'service', 'parishioner']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('parishioner.orders.pdf', [
            'order'  => $order,
            'parish' => [
                'name'    => config('parish.name'),
                'address' => config('parish.address'),
                'phone'   => config('parish.phone'),
                'email'   => config('parish.email'),
            ],
        ])->setPaper('A4', 'portrait');

        return $pdf->download('OP-' . $order->order_number . '.pdf');
    }

    /**
     * List all orders for this parishioner.
     */
    public function index()
    {
        $parishioner = auth()->user()->parishioner;
        if (!$parishioner) {
            return redirect()->route('parishioner.profile');
        }
        $orders = Order::where('parishioner_id', $parishioner->id)
            ->with(['booking', 'service', 'payment'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('parishioner.orders.index', compact('orders'));
    }
}
