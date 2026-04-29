<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Parishioner;
use App\Models\Payment;
use App\Models\SacramentalRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    public function generate(array $params)
    {
        [$from, $to] = $this->resolveDateRange($params);

        $data = $this->collectData($from, $to, $params['category'] ?? null);

        if ($params['type'] === 'pdf') {
            return $this->generatePdf($data, $from, $to);
        }

        return $this->generateExcel($data, $from, $to);
    }

    private function resolveDateRange(array $params): array
    {
        return match ($params['period']) {
            'week'   => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'year'   => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'custom' => [$params['date_from'], $params['date_to']],
            default  => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        };
    }

    private function collectData(string $from, string $to, ?string $category): array
    {
        return [
            'parishioners' => [
                'total'   => Parishioner::count(),
                'new'     => Parishioner::whereBetween('created_at', [$from, $to . ' 23:59:59'])->count(),
                'active'  => Parishioner::where('is_active', true)->count(),
            ],
            'sacraments' => SacramentalRecord::select('type', \DB::raw('count(*) as total'))
                ->whereBetween('date_administered', [$from, $to])
                ->groupBy('type')
                ->pluck('total', 'type')
                ->toArray(),
            'bookings' => [
                'total'     => Booking::whereBetween('scheduled_date', [$from, $to])->count(),
                'pending'   => Booking::whereBetween('scheduled_date', [$from, $to])->where('status', 'pending')->count(),
                'confirmed' => Booking::whereBetween('scheduled_date', [$from, $to])->where('status', 'confirmed')->count(),
                'completed' => Booking::whereBetween('scheduled_date', [$from, $to])->where('status', 'completed')->count(),
                'cancelled' => Booking::whereBetween('scheduled_date', [$from, $to])->where('status', 'cancelled')->count(),
            ],
            'revenue' => [
                'total'      => Payment::paid()->whereBetween('paid_at', [$from, $to . ' 23:59:59'])->sum('amount'),
                'by_method'  => Payment::paid()
                    ->whereBetween('paid_at', [$from, $to . ' 23:59:59'])
                    ->select('payment_method', \DB::raw('sum(amount) as total'))
                    ->groupBy('payment_method')
                    ->pluck('total', 'payment_method')
                    ->toArray(),
                'refunded'   => Payment::where('status', 'refunded')
                    ->whereBetween('refunded_at', [$from, $to . ' 23:59:59'])
                    ->sum('amount'),
            ],
            'period' => ['from' => $from, 'to' => $to],
        ];
    }

    private function generatePdf(array $data, string $from, string $to)
    {
        $pdf = Pdf::loadView('reports.parish-report', [
            'data'   => $data,
            'from'   => $from,
            'to'     => $to,
            'parish' => [
                'name'    => config('parish.name'),
                'address' => config('parish.address'),
            ],
        ])->setPaper('letter', 'portrait');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"parish-report-{$from}-to-{$to}.pdf\"",
        ]);
    }

    private function generateExcel(array $data, string $from, string $to)
    {
        return Excel::download(
            new \App\Exports\ParishReportExport($data),
            "parish-report-{$from}-to-{$to}.xlsx"
        );
    }
}
