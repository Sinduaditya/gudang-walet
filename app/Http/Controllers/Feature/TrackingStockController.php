<?php

namespace App\Http\Controllers\Feature;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Services\Stock\TrackingStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingStockController extends Controller
{
    // public function index(Request $request)
    // {
    //     $perPage = 10; // Tampilkan 15 data per tabel (lebih standar untuk pagination)

    //     $totalStokPerGrade = InventoryTransaction::select(
    //             'grade_company_id',
    //             DB::raw('SUM(quantity_change_grams) as total_grams')
    //         )
    //         ->with('gradeCompany')
    //         ->groupBy('grade_company_id')
    //         ->having('total_grams', '>', 0.01) // Filter langsung di query
    //         ->orderBy('total_grams', 'desc')
    //         ->get();

    //     // 2. Riwayat Penjualan Langsung - SALE_OUT (Tab 2)
    //     $penjualanTransactions = InventoryTransaction::where('transaction_type', 'SALE_OUT')
    //         ->with(['gradeCompany', 'location'])
    //         ->latest('transaction_date')
    //         ->latest('id') // Secondary sort untuk data dengan tanggal sama
    //         ->paginate($perPage, ['*'], 'penjualan_page');

    //     // 3. Riwayat Transfer Internal - TRANSFER_IN/OUT (Tab 3)
    //     $transferInternalIds = InventoryTransaction::where('transaction_type', 'TRANSFER_OUT')
    //         ->pluck('reference_id');

    //     $transferInternalTransactions = InventoryTransaction::whereIn('transaction_type', ['TRANSFER_OUT', 'TRANSFER_IN'])
    //         ->whereIn('reference_id', $transferInternalIds)
    //         ->with(['gradeCompany', 'location'])
    //         ->latest('transaction_date')
    //         ->latest('id')
    //         ->paginate($perPage, ['*'], 'internal_page');

    //     // 4. Riwayat Transfer External - EXTERNAL_TRANSFER_IN (Tab 4)
    //     $transferExternalTransactions = InventoryTransaction::where('transaction_type', 'EXTERNAL_TRANSFER_IN')
    //         ->with(['gradeCompany', 'location', 'stockTransfer.fromLocation'])
    //         ->latest('transaction_date')
    //         ->latest('id')
    //         ->paginate($perPage, ['*'], 'external_page');

    //     return view('admin.stock.tracking', compact(
    //         'totalStokPerGrade',
    //         'penjualanTransactions',
    //         'transferInternalTransactions',
    //         'transferExternalTransactions'
    //     ));
    // }

    protected TrackingStockService $trackingStockService;

    public function __construct(TrackingStockService $trackingStockService)
    {
        $this->trackingStockService = $trackingStockService;
    }

    // public function index(Request $request)
    // {
    //     $search = $request->input('search');

    //     $trackingStocks = $this->trackingStockService->getGradeCompany($search);

    //     return view('admin.stock.index', compact('trackingStocks', 'search'));
    // }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $trackingStocks = $this->trackingStockService->getGradeCompany($search);

        $allGrades = $this->trackingStockService->getAllGradeCompany();

        return view('admin.stock.index', compact(
            'trackingStocks',
            'allGrades',
            'search'
        ));
    }

    public function detail(Request $request, $id)
    {
        $grade = $this->trackingStockService->getGradeById($id);

        $globalStock = $this->trackingStockService->calculateGlobalStock($id);

        // 3. Ambil List Lokasi (Bisa difilter search)
        $search = $request->input('search');
        $locationStocks = $this->trackingStockService->getStockPerLocation($id, $search);

        return view('admin.stock.detail', compact('grade', 'globalStock', 'locationStocks', 'search'));
    }
}
