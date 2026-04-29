<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class ParishReportExport implements WithMultipleSheets
{
    public function __construct(private array $data) {}

    public function sheets(): array
    {
        return [
            new SummarySheet($this->data),
            new SacramentsSheet($this->data),
            new BookingsSheet($this->data),
            new RevenueSheet($this->data),
        ];
    }
}

class SummarySheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $data) {}

    public function title(): string { return 'Summary'; }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    public function array(): array
    {
        $period = $this->data['period'];
        return [
            ['Report Period', $period['from'] . ' to ' . $period['to']],
            ['Total Parishioners', $this->data['parishioners']['total']],
            ['New Parishioners', $this->data['parishioners']['new']],
            ['Active Parishioners', $this->data['parishioners']['active']],
            ['Total Revenue', '₱' . number_format($this->data['revenue']['total'], 2)],
            ['Refunded Amount', '₱' . number_format($this->data['revenue']['refunded'], 2)],
            ['Total Bookings', $this->data['bookings']['total']],
            ['Completed Bookings', $this->data['bookings']['completed']],
            ['Cancelled Bookings', $this->data['bookings']['cancelled']],
        ];
    }
}

class SacramentsSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $data) {}

    public function title(): string { return 'Sacraments'; }

    public function headings(): array
    {
        return ['Sacrament Type', 'Count'];
    }

    public function array(): array
    {
        $labels = ['baptism' => 'Baptism', 'first_communion' => 'First Communion', 'confirmation' => 'Confirmation', 'marriage' => 'Marriage', 'death_burial' => 'Death/Burial'];
        $rows   = [];
        foreach ($labels as $key => $label) {
            $rows[] = [$label, $this->data['sacraments'][$key] ?? 0];
        }
        return $rows;
    }
}

class BookingsSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $data) {}

    public function title(): string { return 'Bookings'; }

    public function headings(): array
    {
        return ['Status', 'Count'];
    }

    public function array(): array
    {
        return [
            ['Total',     $this->data['bookings']['total']],
            ['Pending',   $this->data['bookings']['pending']],
            ['Confirmed', $this->data['bookings']['confirmed']],
            ['Completed', $this->data['bookings']['completed']],
            ['Cancelled', $this->data['bookings']['cancelled']],
        ];
    }
}

class RevenueSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $data) {}

    public function title(): string { return 'Revenue'; }

    public function headings(): array
    {
        return ['Payment Method', 'Total Amount'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data['revenue']['by_method'] as $method => $total) {
            $rows[] = [ucfirst($method), '₱' . number_format($total, 2)];
        }
        $rows[] = ['TOTAL', '₱' . number_format($this->data['revenue']['total'], 2)];
        return $rows;
    }
}
