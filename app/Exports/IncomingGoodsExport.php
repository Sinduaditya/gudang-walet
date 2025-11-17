<?php

namespace App\Exports;

use App\Models\PurchaseReceipt;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomingGoodsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
        
        $query = PurchaseReceipt::with(['supplier', 'receiptItems.gradeSupplier']);

        // Apply date filters
        if (!empty($filters['month'])) {
            $query->whereMonth('receipt_date', $filters['month']);
        }

        if (!empty($filters['year'])) {
            $query->whereYear('receipt_date', $filters['year']);
        }

        $receipts = $query->latest('receipt_date')->get();

        // Transform data to flat structure with items + totals
        $this->data = collect();
        
        foreach ($receipts as $receipt) {
            // Add individual items
            foreach ($receipt->receiptItems as $index => $item) {
                // Calculate percentage
                $percentage = 0;
                if ($item->supplier_weight_grams > 0) {
                    $percentage = ($item->difference_grams / $item->supplier_weight_grams) * 100;
                }

                $this->data->push([
                    'type' => 'item',
                    'receipt_date' => $receipt->receipt_date,
                    'unloading_date' => $receipt->unloading_date,
                    'supplier_name' => $receipt->supplier->name ?? '-',
                    'grade_name' => $item->gradeSupplier->name ?? '-',
                    'supplier_weight' => $item->supplier_weight_grams,
                    'warehouse_weight' => $item->warehouse_weight_grams,
                    'difference' => $item->difference_grams,
                    'percentage' => $percentage,
                    'is_first_item' => $index === 0,
                    'receipt_id' => $receipt->id,
                ]);
            }

            // Add total row for this receipt
            $totalSupplierWeight = $receipt->receiptItems->sum('supplier_weight_grams');
            $totalWarehouseWeight = $receipt->receiptItems->sum('warehouse_weight_grams');
            $totalDifference = $receipt->receiptItems->sum('difference_grams');
            $totalPercentage = $totalSupplierWeight > 0 ? ($totalDifference / $totalSupplierWeight) * 100 : 0;

            $this->data->push([
                'type' => 'total',
                'receipt_date' => null,
                'unloading_date' => null,
                'supplier_name' => '',
                'grade_name' => 'TOTAL',
                'supplier_weight' => $totalSupplierWeight,
                'warehouse_weight' => $totalWarehouseWeight,
                'difference' => $totalDifference,
                'percentage' => $totalPercentage,
                'is_first_item' => false,
                'receipt_id' => $receipt->id,
            ]);

            // Add empty row for separation (except for last receipt)
            $this->data->push([
                'type' => 'separator',
                'receipt_date' => null,
                'unloading_date' => null,
                'supplier_name' => '',
                'grade_name' => '',
                'supplier_weight' => '',
                'warehouse_weight' => '',
                'difference' => '',
                'percentage' => '',
                'is_first_item' => false,
                'receipt_id' => $receipt->id,
            ]);
        }
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $title = 'Data Barang Masuk';
        
        if (!empty($this->filters['month']) && !empty($this->filters['year'])) {
            $monthName = date('F', mktime(0, 0, 0, $this->filters['month'], 1));
            $title .= ' - ' . $monthName . ' ' . $this->filters['year'];
        } elseif (!empty($this->filters['year'])) {
            $title .= ' - Tahun ' . $this->filters['year'];
        } elseif (!empty($this->filters['month'])) {
            $monthName = date('F', mktime(0, 0, 0, $this->filters['month'], 1));
            $title .= ' - ' . $monthName;
        }

        return [
            'Tanggal Datang',
            'Tanggal Bongkar', 
            'Nama Supplier',
            'Grade Supplier',
            'Berat Datang (gr)',
            'Berat Timbang (gr)',
            'Selisih (gr)',
            'Persentase (%)'
        ];
    }

    public function map($row): array
    {
        if ($row['type'] === 'separator') {
            return ['', '', '', '', '', '', '', ''];
        }

        // Format percentage
        $percentageFormatted = '';
        if ($row['type'] === 'item' || $row['type'] === 'total') {
            if (abs($row['percentage']) > 0.001) {
                $percentageFormatted = number_format($row['percentage'], 3, ',', '.');
            }
        }

        // Format difference with sign
        $differenceFormatted = '';
        if ($row['type'] === 'item' || $row['type'] === 'total') {
            $differenceFormatted = $row['difference'];
            if ($row['difference'] > 0) {
                $differenceFormatted = '+' . $row['difference'];
            }
        }

        return [
            ($row['is_first_item'] && $row['type'] === 'item') ? optional($row['receipt_date'])->format('d/m/Y') : '',
            ($row['is_first_item'] && $row['type'] === 'item') ? optional($row['unloading_date'])->format('d/m/Y') : '',
            ($row['is_first_item'] && $row['type'] === 'item') ? $row['supplier_name'] : '',
            $row['grade_name'],
            $row['supplier_weight'] !== '' ? number_format($row['supplier_weight']) : '',
            $row['warehouse_weight'] !== '' ? number_format($row['warehouse_weight']) : '',
            $differenceFormatted,
            $percentageFormatted
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ]
            ]
        ];

        $rowNumber = 2;
        foreach ($this->data as $row) {
            if ($row['type'] === 'total') {
                // Style untuk baris TOTAL
                $styles[$rowNumber] = [
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'] // Yellow background
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        ],
                    ]
                ];
            } elseif ($row['type'] === 'item') {
                // Style based on difference for items
                $difference = (float) $row['difference'];
                if ($difference < 0) {
                    $styles['G' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2']
                        ]
                    ];
                    $styles['H' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true]
                    ];
                } elseif ($difference > 0) {
                    $styles['G' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => '059669'], 'bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'D1FAE5']
                        ]
                    ];
                    $styles['H' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => '059669'], 'bold' => true]
                    ];
                }

                // Border untuk item rows
                $styles['A' . $rowNumber . ':H' . $rowNumber] = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ]
                ];
            }

            $rowNumber++;
        }

        return $styles;
    }
}