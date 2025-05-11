<?php

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerOrdersExport implements FromArray, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $customerId;
    protected $startDate;
    protected $endDate;
    protected $reportService;
    protected $report;

    public function __construct($customerId, $startDate, $endDate)
    {
        $this->customerId = $customerId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reportService = app(ReportService::class);
        $this->report = $this->reportService->generateCustomerOrderReport(
            $this->customerId,
            $this->startDate,
            $this->endDate
        );
    }

    public function array(): array
    {
        return $this->report['supplier_orders']->toArray();
    }

    public function headings(): array
    {
        return [
            'Furnizor',
            'Număr Comenzi',
            'Comenzi Livrate',
            'Comenzi în Așteptare',
            'Valoare Totală (RON)'
        ];
    }

    public function map($row): array
    {
        return [
            $row['name'],
            $row['orders_count'],
            $row['delivered_orders'],
            $row['pending_orders'],
            number_format($row['total_amount'], 2)
        ];
    }

    public function title(): string
    {
        return 'Raport Comenzi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A' => ['width' => 40],
            'B' => ['width' => 15],
            'C' => ['width' => 15],
            'D' => ['width' => 20],
            'E' => ['width' => 20],
        ];
    }
} 