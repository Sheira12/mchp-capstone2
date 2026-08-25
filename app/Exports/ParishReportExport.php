<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ParishReportExport implements WithMultipleSheets
{
    public function __construct(private array $data) {}

    public function sheets(): array
    {
        $sheets = [new SummarySheet($this->data)];

        // Add type-specific sheets based on available data
        if (isset($this->data['sacraments'])) {
            $sheets[] = new SacramentsSheet($this->data);
        }
        if (isset($this->data['bookings'])) {
            $sheets[] = new BookingsSheet($this->data);
        }
        if (isset($this->data['revenue'])) {
            $sheets[] = new RevenueSheet($this->data);
        }
        // New detailed sheets from ReportsController
        if (isset($this->data['parishioners']['by_barangay'])) {
            $sheets[] = new BarangaySheet($this->data);
        }
        if (isset($this->data['revenue']['daily'])) {
            $sheets[] = new DailyCollectionsSheet($this->data);
        }

        return $sheets;
    }
}

// ── Shared style trait ──────────────────────────────────────────────────
trait ExcelStyles
{
    public function styles(Worksheet $sheet): array
    {
        $lastRow  = $sheet->getHighestRow();
        $lastCol  = $sheet->getHighestColumn();
        $dataRange = 'A1:' . $lastCol . $lastRow;

        // Header row style
        $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray([
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Data rows alternating
        for ($row = 2; $row <= $lastRow; $row++) {
            $bg = ($row % 2 === 0) ? 'F9FAFB' : 'FFFFFF';
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // Full border
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']],
            ],
        ]);

        // Outer border thicker
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1D4ED8']]],
        ]);

        // Auto-fit row heights
        for ($row = 2; $row <= $lastRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(18);
        }

        return [];
    }
}

// ── Summary Sheet ────────────────────────────────────────────────────────
class SummarySheet implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithStyles
{
    use ExcelStyles;
    public function __construct(private array $data) {}
    public function title(): string { return 'Summary'; }
    public function headings(): array { return ['Metric', 'Value']; }
    public function columnWidths(): array { return ['A' => 35, 'B' => 25]; }

    public function array(): array
    {
        $period = $this->data['period'] ?? ['from' => 'N/A', 'to' => 'N/A'];
        $rows = [
            ['Report Period',    ($period['from'] ?? '') . ' to ' . ($period['to'] ?? '')],
            ['Quarter',         $period['quarter_label'] ?? 'Custom Range'],
            ['Generated At',    now()->format('F d, Y h:i A')],
            ['Parish Name',     config('parish.name')],
            ['Parish Address',  config('parish.address')],
            ['', ''],
        ];

        if (isset($this->data['parishioners'])) {
            $rows[] = ['=== PARISHIONERS ===', ''];
            $rows[] = ['Total Parishioners',  $this->data['parishioners']['total'] ?? 0];
            $rows[] = ['Active Parishioners', $this->data['parishioners']['active'] ?? 0];
            $rows[] = ['New Parishioners',    $this->data['parishioners']['new'] ?? 0];
            $rows[] = ['Total Families',      $this->data['parishioners']['families'] ?? 0];
            $rows[] = ['', ''];
        }

        if (isset($this->data['revenue'])) {
            $rows[] = ['=== REVENUE ===', ''];
            $rows[] = ['Total Collected', number_format($this->data['revenue']['total_collected'] ?? $this->data['revenue']['total'] ?? 0, 2)];
            $rows[] = ['Total Debit',     number_format($this->data['revenue']['total_debit']     ?? 0, 2)];
            $rows[] = ['Total Credit',    number_format($this->data['revenue']['total_credit']    ?? 0, 2)];
            $rows[] = ['Net Total',       number_format(($this->data['revenue']['total_debit'] ?? 0) - ($this->data['revenue']['total_credit'] ?? 0), 2)];
            $rows[] = ['Refunded Amount', number_format($this->data['revenue']['total_refunded']  ?? $this->data['revenue']['refunded'] ?? 0, 2)];
            $rows[] = ['', ''];
        }

        if (isset($this->data['bookings'])) {
            $rows[] = ['=== BOOKINGS ===', ''];
            $rows[] = ['Total Bookings',     $this->data['bookings']['total'] ?? 0];
            $rows[] = ['Pending',            $this->data['bookings']['pending'] ?? 0];
            $rows[] = ['Confirmed',          $this->data['bookings']['confirmed'] ?? 0];
            $rows[] = ['Completed',          $this->data['bookings']['completed'] ?? 0];
            $rows[] = ['Cancelled',          $this->data['bookings']['cancelled'] ?? 0];
        }

        return $rows;
    }
}

// ── Sacraments Sheet ──────────────────────────────────────────────────────
class SacramentsSheet implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithStyles
{
    use ExcelStyles;
    public function __construct(private array $data) {}
    public function title(): string { return 'Sacraments'; }
    public function headings(): array { return ['Sacrament Type', 'Count', 'Percentage']; }
    public function columnWidths(): array { return ['A' => 28, 'B' => 15, 'C' => 15]; }

    public function array(): array
    {
        $labels = [
            'baptism'         => 'Baptism',
            'first_communion' => 'First Holy Communion',
            'confirmation'    => 'Confirmation',
            'marriage'        => 'Marriage',
            'death_burial'    => 'Death / Burial',
        ];
        $total = array_sum(array_values($this->data['sacraments'] ?? []));
        $rows  = [];
        foreach ($labels as $key => $label) {
            $count = $this->data['sacraments'][$key] ?? 0;
            $pct   = $total > 0 ? round($count / $total * 100, 1) . '%' : '0%';
            $rows[] = [$label, $count, $pct];
        }
        $rows[] = ['TOTAL', $total, '100%'];
        return $rows;
    }
}

// ── Bookings Sheet ────────────────────────────────────────────────────────
class BookingsSheet implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithStyles
{
    use ExcelStyles;
    public function __construct(private array $data) {}
    public function title(): string { return 'Bookings'; }
    public function headings(): array { return ['Status', 'Count', 'Percentage']; }
    public function columnWidths(): array { return ['A' => 20, 'B' => 12, 'C' => 15]; }

    public function array(): array
    {
        $total = $this->data['bookings']['total'] ?? 0;
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        $rows = [];
        foreach ($statuses as $s) {
            $count = $this->data['bookings'][$s] ?? 0;
            $pct   = $total > 0 ? round($count / $total * 100, 1) . '%' : '0%';
            $rows[] = [ucfirst($s), $count, $pct];
        }
        $rows[] = ['TOTAL', $total, '100%'];

        // Booking by type if available
        if (!empty($this->data['bookings']['by_type'])) {
            $rows[] = ['', '', ''];
            $rows[] = ['Service Type Breakdown', '', ''];
            foreach ($this->data['bookings']['by_type'] as $t) {
                $rows[] = [$t['type'], $t['total'], ''];
            }
        }

        return $rows;
    }
}

// ── Revenue Sheet ─────────────────────────────────────────────────────────
class RevenueSheet implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithStyles
{
    use ExcelStyles;
    public function __construct(private array $data) {}
    public function title(): string { return 'Revenue'; }
    public function headings(): array { return ['Payment Method', 'Transactions', 'Total Amount (PHP)', 'Transaction Type', 'Debit (PHP)', 'Credit (PHP)']; }
    public function columnWidths(): array { return ['A' => 22, 'B' => 15, 'C' => 22, 'D' => 18, 'E' => 18, 'F' => 18]; }

    public function array(): array
    {
        $rows     = [];
        $revenue  = $this->data['revenue'] ?? [];
        $byMethod = $revenue['by_method'] ?? [];
        $byType   = $revenue['by_type']   ?? [];

        // Index by_type for quick lookup
        $debitTotal  = 0;
        $creditTotal = 0;
        if (is_iterable($byType)) {
            foreach ($byType as $t) {
                if (is_object($t)) {
                    if ($t->transaction_type === 'debit')  $debitTotal  = (float) $t->total;
                    if ($t->transaction_type === 'credit') $creditTotal = (float) $t->total;
                }
            }
        }

        if (is_array($byMethod) && count($byMethod) && is_object(reset($byMethod))) {
            // From ReportsController (Collection objects)
            foreach ($byMethod as $m) {
                $method = \App\Models\Payment::METHODS[$m->payment_method] ?? ucfirst($m->payment_method);
                $rows[] = [$method, $m->count ?? '—', number_format($m->total, 2), 'Mixed', '—', '—'];
            }
        } else {
            // From DashboardController (plain array)
            foreach ($byMethod as $method => $total) {
                $rows[] = [\App\Models\Payment::METHODS[$method] ?? ucfirst($method), '—', number_format($total, 2), 'Mixed', '—', '—'];
            }
        }

        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['=== TRANSACTION TYPE BREAKDOWN ===', '', '', '', '', ''];
        $rows[] = ['Debit (Fees Paid)',  $revenue['debit_count']  ?? '—', number_format($revenue['total_debit']  ?? 0, 2), 'Debit',  number_format($revenue['total_debit']  ?? 0, 2), '—'];
        $rows[] = ['Credit (Refunds)',   $revenue['credit_count'] ?? '—', number_format($revenue['total_credit'] ?? 0, 2), 'Credit', '—', number_format($revenue['total_credit'] ?? 0, 2)];
        $rows[] = ['NET TOTAL',          ($revenue['debit_count'] ?? 0) + ($revenue['credit_count'] ?? 0),
                    number_format(($revenue['total_debit'] ?? 0) - ($revenue['total_credit'] ?? 0), 2), '', '', ''];

        $rows[] = ['', '', '', '', '', ''];
        $rows[] = ['=== TOTALS ===', '', '', '', '', ''];
        $rows[] = ['TOTAL COLLECTED', '—', number_format($revenue['total_collected'] ?? $revenue['total'] ?? 0, 2), '', '', ''];
        if (!empty($revenue['total_pending']))  $rows[] = ['TOTAL PENDING',  '—', number_format($revenue['total_pending'],  2), '', '', ''];
        if (!empty($revenue['total_refunded'])) $rows[] = ['TOTAL REFUNDED', '—', number_format($revenue['total_refunded'], 2), '', '', ''];

        return $rows;
    }
}

// ── Barangay Breakdown Sheet ───────────────────────────────────────────────
class BarangaySheet implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithStyles
{
    use ExcelStyles;
    public function __construct(private array $data) {}
    public function title(): string { return 'By Barangay'; }
    public function headings(): array { return ['Barangay', 'Parishioner Count']; }
    public function columnWidths(): array { return ['A' => 30, 'B' => 20]; }

    public function array(): array
    {
        $rows = [];
        foreach (($this->data['parishioners']['by_barangay'] ?? []) as $row) {
            $rows[] = [$row->barangay ?? 'Unknown', $row->total];
        }
        return $rows;
    }
}

// ── Daily Collections Sheet ───────────────────────────────────────────────
class DailyCollectionsSheet implements FromArray, WithHeadings, WithTitle, WithColumnWidths, WithStyles
{
    use ExcelStyles;
    public function __construct(private array $data) {}
    public function title(): string { return 'Daily Collections'; }
    public function headings(): array { return ['Date', 'Total Collected (PHP)']; }
    public function columnWidths(): array { return ['A' => 20, 'B' => 25]; }

    public function array(): array
    {
        $rows = [];
        foreach (($this->data['revenue']['daily'] ?? []) as $d) {
            $rows[] = [
                \Carbon\Carbon::parse($d->date)->format('M d, Y'),
                number_format($d->total, 2),
            ];
        }
        return $rows;
    }
}
