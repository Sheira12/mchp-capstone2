<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Family;
use App\Models\Parishioner;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    // ── Main Reports hub ──────────────────────────────────────
    public function index()
    {
        $stats = [
            'total_parishioners' => Parishioner::count(),
            'active_parishioners'=> Parishioner::where('is_active', true)->count(),
            'total_families'     => Family::count(),
            'total_bookings'     => Booking::count(),
            'pending_bookings'   => Booking::where('status', 'pending')->count(),
            'total_revenue'      => Payment::paid()->sum('amount'),
            'outstanding'        => Booking::whereDoesntHave('payment', fn($q) => $q->where('status','paid'))->sum('service_fee'),
        ];
        return view('admin.reports.index', compact('stats'));
    }

    // ── Parishioner Report ────────────────────────────────────
    public function parishioners(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $male   = Parishioner::where('gender', 'male')->count();
        $female = Parishioner::where('gender', 'female')->count();
        $other  = Parishioner::whereNotIn('gender', ['male','female'])->orWhereNull('gender')->count();

        $data = [
            'total'       => Parishioner::count(),
            'active'      => Parishioner::where('is_active', true)->count(),
            'inactive'    => Parishioner::where('is_active', false)->count(),
            'male'        => $male,
            'female'      => $female,
            'other'       => $other,
            'families'    => Family::count(),
            'new'         => Parishioner::whereBetween('created_at', [$from, $to.' 23:59:59'])->count(),
            'by_barangay' => Parishioner::select('barangay', DB::raw('count(*) as total'))
                ->whereNotNull('barangay')->groupBy('barangay')->orderByDesc('total')->limit(10)->get(),
            'monthly'     => Parishioner::select(
                    DB::raw(\DB::getDriverName() === 'pgsql' ? "EXTRACT(YEAR FROM created_at)::integer as year" : "YEAR(created_at) as year"),
                    DB::raw(\DB::getDriverName() === 'pgsql' ? "EXTRACT(MONTH FROM created_at)::integer as month" : "MONTH(created_at) as month"),
                    DB::raw('count(*) as total')
                )->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('year','month')->orderBy('year')->orderBy('month')->get(),
            'from'        => $from,
            'to'          => $to,
        ];

        if ($request->get('export') === 'pdf') {
            return $this->exportPdf('admin.reports.parishioners-pdf', $data, 'parishioner-report');
        }
        if ($request->get('export') === 'excel') {
            return Excel::download(new \App\Exports\ParishReportExport(['parishioners' => $data, 'period' => ['from'=>$from,'to'=>$to]]), 'parishioner-report.xlsx');
        }

        return view('admin.reports.parishioners', compact('data'));
    }

    // ── Payment Report ────────────────────────────────────────
    public function payments(Request $request)
    {
        // ── Quarter presets ──────────────────────────────────────
        $quarters = [
            'q1' => ['label' => '1st Quarter (Jan – Mar)', 'from' => '-01-01', 'to' => '-03-31'],
            'q2' => ['label' => '2nd Quarter (Apr – Jun)', 'from' => '-04-01', 'to' => '-06-30'],
            'q3' => ['label' => '3rd Quarter (Jul – Sep)', 'from' => '-07-01', 'to' => '-09-30'],
            'q4' => ['label' => '4th Quarter (Oct – Dec)', 'from' => '-10-01', 'to' => '-12-31'],
        ];

        $year    = (int) $request->get('year', now()->year);
        $quarter = $request->get('quarter'); // 'q1'|'q2'|'q3'|'q4'|null

        if ($quarter && isset($quarters[$quarter])) {
            $from = $year . $quarters[$quarter]['from'];
            $to   = $year . $quarters[$quarter]['to'];
        } else {
            [$from, $to] = $this->dateRange($request);
        }

        $quarterLabel = ($quarter && isset($quarters[$quarter]))
            ? $quarters[$quarter]['label'] . ' ' . $year
            : null;

        $baseQuery = fn() => Payment::paid()->whereBetween('paid_at', [$from, $to . ' 23:59:59']);

        $data = [
            'total_collected'  => ($baseQuery)()->sum('amount'),
            'total_debit'      => ($baseQuery)()->debit()->sum('amount'),
            'total_credit'     => ($baseQuery)()->credit()->sum('amount'),
            'total_pending'    => Payment::where('status', 'pending')->sum('amount'),
            'total_refunded'   => Payment::where('status', 'refunded')
                                    ->whereBetween('refunded_at', [$from, $to . ' 23:59:59'])
                                    ->sum('amount'),
            'debit_count'      => ($baseQuery)()->debit()->count(),
            'credit_count'     => ($baseQuery)()->credit()->count(),
            'by_method'        => ($baseQuery)()
                ->select('payment_method', DB::raw('sum(amount) as total'), DB::raw('count(*) as count'))
                ->groupBy('payment_method')->get(),
            'by_type'          => ($baseQuery)()
                ->select('transaction_type', DB::raw('sum(amount) as total'), DB::raw('count(*) as count'))
                ->groupBy('transaction_type')->get(),
            'daily'            => ($baseQuery)()
                ->select(DB::raw('DATE(paid_at) as date'), DB::raw('sum(amount) as total'), DB::raw('count(*) as count'))
                ->groupBy('date')->orderBy('date')->get(),
            'monthly'          => Payment::paid()
                ->where('paid_at', '>=', now()->subMonths(12))
                ->select(
                    DB::raw(\DB::getDriverName() === 'pgsql' ? "EXTRACT(YEAR FROM paid_at)::integer as year" : "YEAR(paid_at) as year"),
                    DB::raw(\DB::getDriverName() === 'pgsql' ? "EXTRACT(MONTH FROM paid_at)::integer as month" : "MONTH(paid_at) as month"),
                    DB::raw('sum(amount) as total')
                )
                ->groupBy('year', 'month')->orderBy('year')->orderBy('month')->get(),
            'outstanding_count' => Booking::whereDoesntHave('payment', fn($q) => $q->where('status', 'paid'))->count(),
            'outstanding_amt'   => Booking::whereDoesntHave('payment', fn($q) => $q->where('status', 'paid'))->sum('service_fee'),
            'from'              => $from,
            'to'                => $to,
            'quarter'           => $quarter,
            'quarter_label'     => $quarterLabel,
            'year'              => $year,
            'quarters'          => $quarters,
        ];

        if ($request->get('export') === 'pdf') {
            return $this->exportPdf('admin.reports.payments-pdf', $data, 'payment-report');
        }
        if ($request->get('export') === 'excel') {
            return Excel::download(
                new \App\Exports\ParishReportExport(['revenue' => $data, 'period' => ['from' => $from, 'to' => $to, 'quarter_label' => $quarterLabel]]),
                'payment-report' . ($quarter ? "-{$quarter}-{$year}" : '') . '.xlsx'
            );
        }

        return view('admin.reports.payments', compact('data'));
    }

    // ── Booking Report ────────────────────────────────────────
    public function bookings(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $base = Booking::whereBetween('scheduled_date', [$from, $to]);

        $data = [
            'total'       => (clone $base)->count(),
            'pending'     => (clone $base)->where('status','pending')->count(),
            'confirmed'   => (clone $base)->where('status','confirmed')->count(),
            'completed'   => (clone $base)->where('status','completed')->count(),
            'cancelled'   => (clone $base)->where('status','cancelled')->count(),
            'by_type'     => (clone $base)->select('booking_type', DB::raw('count(*) as total'))
                ->groupBy('booking_type')->orderByDesc('total')->get()
                ->map(fn($r) => ['type' => Booking::TYPES[$r->booking_type] ?? $r->booking_type, 'total' => $r->total]),
            'monthly'     => Booking::select(
                    DB::raw(\DB::getDriverName() === 'pgsql' ? "EXTRACT(YEAR FROM scheduled_date)::integer as year" : "YEAR(scheduled_date) as year"),
                    DB::raw(\DB::getDriverName() === 'pgsql' ? "EXTRACT(MONTH FROM scheduled_date)::integer as month" : "MONTH(scheduled_date) as month"),
                    DB::raw('count(*) as total')
                )->where('scheduled_date','>=', now()->subMonths(12))
                ->groupBy('year','month')->orderBy('year')->orderBy('month')->get(),
            'revenue'     => (clone $base)->join('payments','bookings.id','=','payments.booking_id')
                ->where('payments.status','paid')->sum('payments.amount'),
            'from'        => $from,
            'to'          => $to,
        ];

        if ($request->get('export') === 'pdf') {
            return $this->exportPdf('admin.reports.bookings-pdf', $data, 'booking-report');
        }
        if ($request->get('export') === 'excel') {
            return Excel::download(new \App\Exports\ParishReportExport(['bookings' => $data, 'period' => ['from'=>$from,'to'=>$to]]), 'booking-report.xlsx');
        }

        return view('admin.reports.bookings', compact('data'));
    }

    // ── Unified Export ────────────────────────────────────────
    public function export(Request $request)
    {
        $type = $request->get('report', 'parishioners');
        return redirect()->route('admin.reports.' . $type, $request->query() + ['export' => $request->get('format', 'pdf')]);
    }

    // ── Helpers ───────────────────────────────────────────────
    private function dateRange(Request $request): array
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->endOfMonth()->toDateString());
        return [$from, $to];
    }

    private function exportPdf(string $view, array $data, string $filename)
    {
        $pdf = Pdf::loadView($view, [
            'data'      => $data,
            'parish'    => [
                'name'    => config('parish.name'),
                'address' => config('parish.address'),
                'phone'   => config('parish.phone'),
                'email'   => config('parish.email'),
                'priest'  => config('parish.priest'),
            ],
            'logoPath'  => public_path('images/parish-logo.png'),
            'printedAt' => now()->format('F d, Y h:i A'),
        ])
        ->setPaper('A4', 'portrait')
        ->setOption(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => false]);

        return $pdf->download("{$filename}-" . now()->format('Y-m-d') . ".pdf");
    }
}
