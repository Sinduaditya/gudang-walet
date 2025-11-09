<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\Supplier\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index()
    {
        $suppliers = $this->supplierService->getAll();
        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $supplier = $this->supplierService->create($request->all());
        return redirect()->route('suppliers.index')->with('success', 'Data supplier berhasil ditambahkan');
    }

    public function edit($id)
    {
        $supplier = $this->supplierService->getById($id);
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $this->supplierService->update($id, $request->all());
        return redirect()->route('suppliers.index')->with('success', 'Data supplier berhasil diperbarui');
    }

    public function destroy($id)
    {
        $this->supplierService->delete($id);
        return redirect()->route('suppliers.index')->with('success', 'Data supplier berhasil dihapus');
    }
}
