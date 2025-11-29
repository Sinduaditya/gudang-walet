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
                // ✅ FIX: Calculate percentage correctly (selalu positif)
                $percentage = 0;
                $decimal = 0;
                if ($item->supplier_weight_grams > 0) {
                    $decimal = $item->difference_grams / $item->supplier_weight_grams; // Bisa negatif/positif
                    $percentage = abs($decimal) * 100; // ✅ Selalu positif untuk persentase
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
                    'percentage' => $percentage, // ✅ Selalu positif
                    'decimal_ratio' => $decimal, // ✅ Tambah rasio desimal
                    'is_first_item' => $index === 0,
                    'receipt_id' => $receipt->id,
                ]);
            }

            // Add total row for this receipt
            $totalSupplierWeight = $receipt->receiptItems->sum('supplier_weight_grams');
            $totalWarehouseWeight = $receipt->receiptItems->sum('warehouse_weight_grams');
            $totalDifference = $receipt->receiptItems->sum('difference_grams');
            
            // ✅ FIX: Total percentage juga selalu positif
            $totalDecimal = $totalSupplierWeight > 0 ? ($totalDifference / $totalSupplierWeight) : 0;
            $totalPercentage = abs($totalDecimal) * 100; // ✅ Selalu positif

            $this->data->push([
                'type' => 'total',
                'receipt_date' => null,
                'unloading_date' => null,
                'supplier_name' => '',
                'grade_name' => 'TOTAL',
                'supplier_weight' => $totalSupplierWeight,
                'warehouse_weight' => $totalWarehouseWeight,
                'difference' => $totalDifference,
                'percentage' => $totalPercentage, // ✅ Selalu positif
                'decimal_ratio' => $totalDecimal, // ✅ Tambah total rasio desimal
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
                'decimal_ratio' => '',
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
            'Rasio Desimal', // ✅ Tambah kolom rasio desimal
            'Persentase (%)'
        ];
    }

    public function map($row): array
    {
        if ($row['type'] === 'separator') {
            return ['', '', '', '', '', '', '', '', ''];
        }

        // ✅ FIX: Format persentase (bulat atau 1 desimal, selalu positif)
        $percentageFormatted = '';
        if ($row['type'] === 'item' || $row['type'] === 'total') {
            if ($row['percentage'] > 0) {
                // ✅ Jika bulat, tampilkan tanpa desimal. Jika tidak, 1 desimal
                if ($row['percentage'] == floor($row['percentage'])) {
                    $percentageFormatted = number_format($row['percentage'], 0, ',', '.');
                } else {
                    $percentageFormatted = number_format($row['percentage'], 1, ',', '.');
                }
            } else {
                $percentageFormatted = '0';
            }
        }

        // ✅ FIX: Format rasio desimal (3 desimal, bisa negatif/positif)
        $decimalFormatted = '';
        if ($row['type'] === 'item' || $row['type'] === 'total') {
            if (isset($row['decimal_ratio']) && abs($row['decimal_ratio']) > 0.0001) {
                $decimalFormatted = number_format($row['decimal_ratio'], 3, ',', '.');
            } else {
                $decimalFormatted = '0,000';
            }
        }

        // ✅ Format selisih dengan status Indonesia
        $differenceFormatted = '';
        if ($row['type'] === 'item' || $row['type'] === 'total') {
            if ($row['difference'] > 0) {
                $differenceFormatted = '+' . number_format($row['difference'], 0, ',', '.') . ' (kelebihan)';
            } elseif ($row['difference'] < 0) {
                $differenceFormatted = number_format($row['difference'], 0, ',', '.') . ' (susut)';
            } else {
                $differenceFormatted = '0 (sama)';
            }
        }

        return [
            ($row['is_first_item'] && $row['type'] === 'item') ? optional($row['receipt_date'])->format('d/m/Y') : '',
            ($row['is_first_item'] && $row['type'] === 'item') ? optional($row['unloading_date'])->format('d/m/Y') : '',
            ($row['is_first_item'] && $row['type'] === 'item') ? $row['supplier_name'] : '',
            $row['grade_name'],
            $row['supplier_weight'] !== '' ? number_format($row['supplier_weight'], 0, ',', '.') : '', // ✅ Format Indonesia
            $row['warehouse_weight'] !== '' ? number_format($row['warehouse_weight'], 0, ',', '.') : '', // ✅ Format Indonesia
            $differenceFormatted,
            $decimalFormatted, // ✅ Rasio desimal
            $percentageFormatted // ✅ Persentase
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
                // ✅ Style based on percentage threshold (5%)
                $percentage = (float) $row['percentage'];
                $difference = (float) $row['difference'];
                
                if ($percentage > 5) { // ✅ 5% threshold
                    // ✅ Red styling untuk selisih dan persentase di atas 5%
                    $styles['G' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2']
                        ]
                    ];
                    $styles['H' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2']
                        ]
                    ];
                    $styles['I' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2']
                        ]
                    ];
                } elseif ($difference < 0) {
                    // ✅ Light red untuk susut tapi di bawah 5%
                    $styles['G' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => 'DC2626']]
                    ];
                } elseif ($difference > 0) {
                    // ✅ Green untuk kelebihan
                    $styles['G' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => '059669']]
                    ];
                }

                // Border untuk item rows
                $styles['A' . $rowNumber . ':I' . $rowNumber] = [
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