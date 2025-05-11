<?php

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierSalesExport implements FromArray, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $supplierId;
    protected $startDate;
    protected $endDate;
    protected $reportService;
    protected $report;

    public function __construct($supplierId, $startDate, $endDate)
    {
        $this->supplierId = $supplierId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reportService = app(ReportService::class);
        $this->report = $this->reportService->generateSupplierSalesReport(
            $this->supplierId,
            $this->startDate,
            $this->endDate
        );
    }

    public function array(): array
    {
        return $this->report['product_sales']->toArray();
    }

    public function headings(): array
    {
        return [
            'Cod Produs',
            'Descriere',
            'Cantitate',
            'Valoare Totală (RON)'
        ];
    }

    public function map($row): array
    {
        return [
            $row['code'],
            $row['description'],
            $row['quantity'],
            number_format($row['total_amount'], 2)
        ];
    }

    public function title(): string
    {
        return 'Raport Vânzări';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A' => ['width' => 15],
            'B' => ['width' => 40],
            'C' => ['width' => 15],
            'D' => ['width' => 20],
        ];
    }
} 