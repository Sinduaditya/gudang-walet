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

    public function __construct()
    {
        $receipts = PurchaseReceipt::with(['supplier', 'receiptItems.gradeSupplier'])
            ->latest('receipt_date')
            ->get();

        // Transform data to flat structure with items
        $this->data = collect();
        
        foreach ($receipts as $receipt) {
            foreach ($receipt->receiptItems as $index => $item) {
                // Calculate percentage
                $percentage = 0;
                if ($item->supplier_weight_grams > 0) {
                    $percentage = ($item->difference_grams / $item->supplier_weight_grams) * 100;
                }

                $this->data->push([
                    'receipt_date' => $receipt->receipt_date,
                    'unloading_date' => $receipt->unloading_date,
                    'supplier_name' => $receipt->supplier->name ?? '-',
                    'grade_name' => $item->gradeSupplier->name ?? '-',
                    'supplier_weight' => $item->supplier_weight_grams,
                    'warehouse_weight' => $item->warehouse_weight_grams,
                    'difference' => $item->difference_grams,
                    'percentage' => $percentage,
                    'is_first_item' => $index === 0, // untuk tampilan tanggal
                    'receipt_id' => $receipt->id,
                    'total_items' => $receipt->receiptItems->count()
                ]);
            }
        }
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
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
        // Format percentage
        $percentageFormatted = '';
        if (abs($row['percentage']) > 0.001) { // jika lebih dari 0.001%
            $percentageFormatted = number_format($row['percentage'], 3, ',', '.');
        }

        // Format difference with sign
        $differenceFormatted = $row['difference'];
        if ($row['difference'] > 0) {
            $differenceFormatted = '+' . $row['difference'];
        }

        return [
            $row['is_first_item'] ? optional($row['receipt_date'])->format('d/m/Y') : '',
            $row['is_first_item'] ? optional($row['unloading_date'])->format('d/m/Y') : '',
            $row['is_first_item'] ? $row['supplier_name'] : '',
            $row['grade_name'],
            number_format($row['supplier_weight']),
            number_format($row['warehouse_weight']),
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

        // Style untuk data rows
        $rowNumber = 2;
        $currentReceiptId = null;

        foreach ($this->data as $row) {
            // New receipt group
            if ($currentReceiptId !== $row['receipt_id']) {
                $currentReceiptId = $row['receipt_id'];
                
                // Style untuk baris pertama setiap receipt (dengan tanggal)
                if ($row['is_first_item']) {
                    $styles[$rowNumber] = [
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F3F4F6']
                        ],
                        'borders' => [
                            'top' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            ],
                        ]
                    ];
                }
            }

            // Style based on difference
            $difference = (float) $row['difference'];
            if ($difference < 0) {
                // Negative (susut) - red
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
                // Positive (bertambah) - green
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

            // Border untuk semua cell
            $styles['A' . $rowNumber . ':H' . $rowNumber] = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ]
            ];

            $rowNumber++;
        }

        return $styles;
    }
}