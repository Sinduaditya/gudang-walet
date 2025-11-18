<?php
namespace App\Exports;

use App\Services\GradingGoods\GradingGoodsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GradingGoodsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $filters;
    protected $gradingGoodsService;
    protected $totalRows = [];

    public function __construct(GradingGoodsService $gradingGoodsService, $filters = [])
    {
        $this->gradingGoodsService = $gradingGoodsService;
        $this->filters = $filters;
        $this->prepareData();
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
            'Berat Hasil Grading (gram)' // Pastikan header menyebutkan gram
        ];
    }

    private function prepareData()
    {
        // Ambil data dengan filter
        $rawData = $this->gradingGoodsService->getAllGrading($this->filters);

        // Group data by grading_date and supplier
        $groupedData = $rawData->groupBy(function($item) {
            $gradingDate = $item->grading_date ? \Carbon\Carbon::parse($item->grading_date)->format('Y-m-d') : '0000-00-00';
            $supplierName = $item->supplier_name ?? 'Unknown Supplier';
            return $gradingDate . '|' . $supplierName;
        });

        $this->data = collect();

        foreach ($groupedData as $groupKey => $items) {
            [$gradingDate, $supplierName] = explode('|', $groupKey);
            
            $first = true;
            $groupTotalWeight = 0;
            $groupTotalQuantity = 0;

            foreach ($items as $item) {
                $receiptDate = $item->receipt_date ? \Carbon\Carbon::parse($item->receipt_date)->format('Y-m-d') : null;
                $gradeSupplierName = $item->grade_supplier_name ?? '-';
                $gradeCompanyName = $item->grade_company_name ?? '-';
                $quantity = $item->quantity ?? 0;
                
                // PASTIKAN weight_grams tetap dalam gram (tidak dikonversi)
                $weightGrams = floatval($item->weight_grams ?? 0);
                
                $groupTotalWeight += $weightGrams;
                $groupTotalQuantity += $quantity;

                $this->data->push([
                    'type' => 'item',
                    'receipt_date' => $receiptDate,
                    'supplier_name' => $supplierName,
                    'grade_supplier_name' => $gradeSupplierName,
                    'grade_company_name' => $gradeCompanyName,
                    'quantity' => $quantity,
                    'weight_grams' => $weightGrams, // Tetap simpan sebagai gram
                    'is_first_item' => $first,
                    'group_key' => $groupKey
                ]);
                $first = false;
            }

            // Add TOTAL row for this group
            $this->data->push([
                'type' => 'total',
                'receipt_date' => null,
                'supplier_name' => '',
                'grade_supplier_name' => '',
                'grade_company_name' => 'TOTAL',
                'quantity' => $groupTotalQuantity,
                'weight_grams' => $groupTotalWeight, // Total juga dalam gram
                'is_first_item' => false,
                'group_key' => $groupKey
            ]);

            // Add separator row between groups (except for last group)
            if ($groupKey !== $groupedData->keys()->last()) {
                $this->data->push([
                    'type' => 'separator',
                    'receipt_date' => null,
                    'supplier_name' => '',
                    'grade_supplier_name' => '',
                    'grade_company_name' => '',
                    'quantity' => '',
                    'weight_grams' => '',
                    'is_first_item' => false,
                    'group_key' => $groupKey
                ]);
            }
        }
    }

    public function map($row): array
    {
        if ($row['type'] === 'separator') {
            return ['', '', '', '', '', ''];
        }

        return [
            // Tanggal Datang (hanya tampil di baris pertama grup)
            ($row['is_first_item'] && $row['type'] === 'item' && $row['receipt_date']) 
                ? \Carbon\Carbon::parse($row['receipt_date'])->format('d/m/Y') 
                : '',
            
            // Nama Supplier (hanya tampil di baris pertama grup)
            ($row['is_first_item'] && $row['type'] === 'item') 
                ? $row['supplier_name'] 
                : '',
            
            // Nama Grade Supplier (hanya tampil di baris pertama grup)
            ($row['is_first_item'] && $row['type'] === 'item') 
                ? $row['grade_supplier_name'] 
                : '',
            
            // Nama Grade Company
            $row['grade_company_name'],
            
            // Jumlah Item
            $row['quantity'] !== '' ? number_format($row['quantity'], 0, ',', '.') : '',
            
            // Berat dalam gram TANPA pembagian atau konversi apapun
            $row['weight_grams'] !== '' ? number_format($row['weight_grams'], 1, ',', '.') : ''
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'] // Gray background
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12); // Tanggal Datang
        $sheet->getColumnDimension('B')->setWidth(20); // Nama Supplier
        $sheet->getColumnDimension('C')->setWidth(20); // Nama Grade Supplier
        $sheet->getColumnDimension('D')->setWidth(18); // Nama Grade Company
        $sheet->getColumnDimension('E')->setWidth(12); // Jumlah Item
        $sheet->getColumnDimension('F')->setWidth(20); // Berat (gram)

        $rowNumber = 2;
        foreach ($this->data as $row) {
            if ($row['type'] === 'total') {
                // Style untuk baris TOTAL
                $styles[$rowNumber] = [
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'] // Yellow background
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ];

                // Store total row index for reference
                $this->totalRows[] = $rowNumber;
                
            } elseif ($row['type'] === 'item') {
                // Style untuk baris item
                $styles['A' . $rowNumber . ':F' . $rowNumber] = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ];

                // Right align untuk kolom angka
                $styles['E' . $rowNumber] = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT
                    ]
                ];
                
                $styles['F' . $rowNumber] = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT
                    ]
                ];
            }

            $rowNumber++;
        }

        return $styles;
    }
}