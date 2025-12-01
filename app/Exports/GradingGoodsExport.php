<?php

namespace App\Exports;

use App\Services\GradingGoods\GradingGoodsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradingGoodsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $filters;
    protected $gradingGoodsService;

    public function __construct(GradingGoodsService $gradingGoodsService, $filters = [])
    {
        $this->gradingGoodsService = $gradingGoodsService;
        $this->filters = $filters;
        $this->prepareData();
    }

    private function prepareData()
    {
        $gradingItems = $this->gradingGoodsService->getAllGradingForExport($this->filters);
        
        $this->data = collect();

        if ($gradingItems->isEmpty()) {
            $this->data->push([
                'type' => 'no_data',
                'grading_date' => null,
                'supplier_name' => 'Tidak ada data grading',
                'grade_supplier_name' => 'untuk filter yang dipilih',
                'grade_company_name' => '',
                'quantity' => '',
                'grading_weight' => '',
                'warehouse_weight' => '',
                'difference' => '',
                'is_first_item' => false,
                'receipt_item_id' => null,
            ]);
            return;
        }

        foreach ($gradingItems as $gradingItem) {
            $sortingResults = $this->gradingGoodsService->getSortingResultsByReceiptItem($gradingItem->receipt_item_id);
            
            $warehouseWeight = $gradingItem->warehouse_weight_grams ?? 0;
            $totalGradingWeight = $gradingItem->total_grading_weight ?? 0;
            $totalQuantity = $sortingResults->sum('quantity');
            $difference = $totalGradingWeight - $warehouseWeight;

            $this->data->push([
                'type' => 'header',
                'grading_date' => $gradingItem->grading_date,
                'supplier_name' => $gradingItem->supplier_name,
                'grade_supplier_name' => $gradingItem->grade_supplier_name,
                'grade_company_name' => '',
                'quantity' => '',
                'grading_weight' => '',
                'warehouse_weight' => $warehouseWeight,
                'difference' => '',
                'is_first_item' => true,
                'receipt_item_id' => $gradingItem->receipt_item_id,
            ]);

            // ✅ Detail grading results
            foreach ($sortingResults as $result) {
                $this->data->push([
                    'type' => 'detail',
                    'grading_date' => null,
                    'supplier_name' => '',
                    'grade_supplier_name' => '',
                    'grade_company_name' => $result->gradeCompany->name ?? '-',
                    'quantity' => $result->quantity ?? 0,
                    'grading_weight' => $result->weight_grams ?? 0,
                    'warehouse_weight' => '',
                    'difference' => '',
                    'is_first_item' => false,
                    'receipt_item_id' => $gradingItem->receipt_item_id,
                ]);
            }

            // ✅ Total row untuk setiap receipt item
            $this->data->push([
                'type' => 'total',
                'grading_date' => null,
                'supplier_name' => '',
                'grade_supplier_name' => '',
                'grade_company_name' => 'TOTAL',
                'quantity' => $totalQuantity,
                'grading_weight' => $totalGradingWeight,
                'warehouse_weight' => $warehouseWeight,
                'difference' => $difference,
                'is_first_item' => false,
                'receipt_item_id' => $gradingItem->receipt_item_id,
            ]);

            // ✅ Empty row untuk separator
            $this->data->push([
                'type' => 'separator',
                'grading_date' => null,
                'supplier_name' => '',
                'grade_supplier_name' => '',
                'grade_company_name' => '',
                'quantity' => '',
                'grading_weight' => '',
                'warehouse_weight' => '',
                'difference' => '',
                'is_first_item' => false,
                'receipt_item_id' => $gradingItem->receipt_item_id,
            ]);
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
            'Nama Supplier', 
            'Nama Grade Supplier',
            'Nama Grade Company',
            'Jumlah Item',
            'Berat Hasil Grading (gram)',
            'Total Berat Barang Gudang (gram)',
            'Selisih (gram)'
        ];
    }

    public function map($row): array
    {
        if ($row['type'] === 'separator') {
            return ['', '', '', '', '', '', '', ''];
        }

        // ✅ Format tanggal
        $dateFormatted = '';
        if ($row['grading_date'] && $row['is_first_item']) {
            $dateFormatted = \Carbon\Carbon::parse($row['grading_date'])->format('d/m/Y');
        }

        if ($row['type'] === 'header') {
            // ✅ Header row - tampilkan supplier info dan berat gudang
            return [
                $dateFormatted, // Tanggal
                $row['supplier_name'], // Supplier
                $row['grade_supplier_name'], // Grade Supplier
                '', // Grade Company (kosong untuk header)
                '', // Jumlah (kosong untuk header)
                '', // Berat Hasil (kosong untuk header)
                $row['warehouse_weight'] ? number_format($row['warehouse_weight'], 1, ',', '.') : '0,0', // Berat Gudang
                '' // Selisih (kosong untuk header)
            ];
        } elseif ($row['type'] === 'detail') {
            // ✅ Detail row - tampilkan grade company dan berat
            return [
                '', // Tanggal (kosong)
                '', // Supplier (kosong)
                '', // Grade Supplier (kosong)
                $row['grade_company_name'], // Grade Company
                $row['quantity'] ? number_format($row['quantity'], 0, ',', '.') : '0', // Jumlah
                $row['grading_weight'] ? number_format($row['grading_weight'], 1, ',', '.') : '0,0', // Berat Hasil
                '', // Berat Gudang (kosong untuk detail)
                '' // Selisih (kosong untuk detail)
            ];
        } elseif ($row['type'] === 'total') {
            // ✅ Total row
            $differenceFormatted = '';
            if ($row['difference'] != 0) {
                $differenceFormatted = ($row['difference'] > 0 ? '+' : '') . number_format($row['difference'], 1, ',', '.');
            } else {
                $differenceFormatted = '0,0';
            }

            return [
                '', // Tanggal (kosong)
                '', // Supplier (kosong)
                '', // Grade Supplier (kosong)
                'TOTAL', // "TOTAL"
                $row['quantity'] ? number_format($row['quantity'], 0, ',', '.') : '0', // Total Jumlah
                $row['grading_weight'] ? number_format($row['grading_weight'], 1, ',', '.') : '0,0', // Total Berat Hasil
                $row['warehouse_weight'] ? number_format($row['warehouse_weight'], 1, ',', '.') : '0,0', // Total Berat Gudang
                $differenceFormatted // Selisih
            ];
        } elseif ($row['type'] === 'no_data') {
            // ✅ No data row
            return [
                '', // Tanggal
                $row['supplier_name'], // "Tidak ada data grading"
                $row['grade_supplier_name'], // "untuk filter yang dipilih"
                '', // Grade Company
                '', // Jumlah
                '', // Berat Hasil
                '', // Berat Gudang
                '' // Selisih
            ];
        }

        return ['', '', '', '', '', '', '', ''];
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
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ]
        ];

        $rowNumber = 2;
        foreach ($this->data as $row) {
            if ($row['type'] === 'header') {
                // ✅ Style untuk header (supplier info)
                $styles[$rowNumber] = [
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBEAFE'] // Light blue background
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ]
                ];
            } elseif ($row['type'] === 'total') {
                // ✅ Style untuk total row
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

                // ✅ Style untuk selisih column (H = kolom 8)
                $difference = (float) ($row['difference'] ?? 0);
                if ($difference != 0) {
                    $colorClass = $difference > 0 ? '059669' : 'DC2626'; // Green atau Red
                    $bgClass = $difference > 0 ? 'D1FAE5' : 'FEE2E2'; // Light green atau Light red
                    
                    $styles['H' . $rowNumber] = [
                        'font' => ['color' => ['rgb' => $colorClass], 'bold' => true],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $bgClass]
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            ],
                        ]
                    ];
                }
            } elseif ($row['type'] === 'detail') {
                // ✅ Style untuk detail rows
                $styles['A' . $rowNumber . ':H' . $rowNumber] = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ]
                ];
            } elseif ($row['type'] === 'no_data') {
                // ✅ Style untuk no data row
                $styles[$rowNumber] = [
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'DC2626']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2'] // Light red background
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ]
                ];
            }

            $rowNumber++;
        }

        return $styles;
    }
}