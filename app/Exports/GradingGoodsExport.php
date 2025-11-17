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

class GradingGoodsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $gradingGoodsService;

    /**
     * Inject service dan ambil data sama persis seperti di halaman index.
     */
    public function __construct(GradingGoodsService $gradingGoodsService)
    {
        $this->gradingGoodsService = $gradingGoodsService;
        $this->data = $this->gradingGoodsService->getAllGrading();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->data;
    }

    /**
     * Mendefinisikan header kolom di Excel.
     */
    public function headings(): array
    {
        return [
            'Tgl Grading',
            'Nama Grade Supplier',
            'Nama Grade Perusahaan',
            'Tgl Kedatangan',
            'Jumlah Item',
            'Berat Gudang (g)',
            'Berat setelah Grading (g)',
            '% Selisih',
            'Catatan',
        ];
    }

    /**
     * Memetakan data dari collection ke format array untuk Excel.
     */
    public function map($row): array
    {
        return [
            $row->grading_date ? \Carbon\Carbon::parse($row->grading_date)->format('d/m/Y') : '-',
            $row->grade_supplier_name ?? '-',
            $row->grade_company_name ?? '-',
            $row->receipt_date ? \Carbon\Carbon::parse($row->receipt_date)->format('d/m/Y') : '-',
            $row->quantity ?? '-',
            // Menggunakan format number yang benar untuk Excel (koma desimal, titik ribuan)
            $row->warehouse_weight_grams !== null ? number_format($row->warehouse_weight_grams, 2, ',', '.') : '-',
            $row->weight_grams !== null ? number_format($row->weight_grams, 2, ',', '.') : '-',
            $row->percentage_difference !== null ? number_format($row->percentage_difference, 2, ',', '.') . ' %' : '-',
            $row->notes ?? '-',
        ];
    }

    /**
     * Memberi styling pada header dan data, termasuk logika merah.
     */
    public function styles(Worksheet $sheet)
    {
        // 1. Style Header (Baris 1)
        // Kolom A sampai I, Baris 1
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB'] // Abu-abu muda
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ]
        ]);

        // 2. Style Data Rows (Mulai dari baris 2)
        $rowNumber = 2;
        foreach ($this->data as $row) {

            // Terapkan border ke seluruh baris
            $sheet->getStyle('A' . $rowNumber . ':I' . $rowNumber)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ]
            ]);

            // Logika selisih > 1.5% (Kolom H)
            $percentageCell = 'H' . $rowNumber;
            if ($row->percentage_difference !== null) {
                // Ambil nilai absolut (minus juga dihitung)
                $selisih = abs((float)$row->percentage_difference);

                if ($selisih > 1.5) {
                    // Jika selisih > 1.5, warnai cell
                    $sheet->getStyle($percentageCell)->applyFromArray([
                        'font' => ['color' => ['rgb' => 'DC2626'], 'bold' => true], // Merah
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2'] // Merah muda
                        ]
                    ]);
                }
            }
            $rowNumber++;
        }
    }
}
