<?php

namespace App\Services\Supplier;

use App\Models\Supplier;

use App\Exports\SuppliersExport;
use Maatwebsite\Excel\Facades\Excel;

class SupplierService
{
    /**
     * Get all suppliers.
     */
    public function getAll()
    {
        return Supplier::latest()->paginate(10); 
    }

    /**
     * Export All Suppliers
     * 
     */

    public function exportToExcel(){
        return Excel::download(new SuppliersExport, 'suppliers-' . date('Y-m-d') . '.xlsx');
    }


    /**
     * Get a single supplier by ID.
     */
    public function getById(int $id)
    {
        return Supplier::findOrFail($id);
    }

    /**
     * Create a new supplier.
     */
    public function create(array $data)
    {
        return Supplier::create($data);
    }

    /**
     * Update an existing supplier.
     */
    public function update(int $id, array $data)
    {
        $supplier = $this->getById($id);
        $supplier->update($data);

        return $supplier;
    }

    /**
     * Delete a supplier.
     */
    public function delete(int $id)
    {
        $supplier = $this->getById($id);
        $supplier->delete();

        return true;
    }
}
