# Add Supplier Tracking to Stock Detail (Migration Approach)

## Goal Description
Display accurate stock breakdown by supplier on the stock detail page. To achieve maximum accuracy as requested, we will add a `supplier_id` column to the `inventory_transactions` table.

## User Review Required
> [!IMPORTANT]
> **Migration & Backfill**: We will add `supplier_id` and attempt to **backfill** it for existing `GRADING_IN` transactions (by tracing back to the Purchase Receipt).
> **Outgoing Stock**: For existing outgoing transactions, `supplier_id` will remain `NULL` (Unknown). For future outgoing transactions, we can implement logic to deduct from specific suppliers (FIFO or manual selection) in a future iteration, or currently leave it as NULL (General Stock) if the user only cares about "Incoming Source".
> **Display**: The Stock Detail page will group stock by Supplier.

## Proposed Changes

### Database
#### [NEW] [Migration](file:///d:/walet/gudang-walet/database/migrations/xxxx_xx_xx_add_supplier_id_to_inventory_transactions.php)
- Add `supplier_id` column to `inventory_transactions` (nullable, foreign key to `suppliers`).
- **Run Script**: Update existing `inventory_transactions` where `transaction_type = 'GRADING_IN'` by joining `sorting_results` -> `receipt_items` -> `purchase_receipts`.

### Models
#### [MODIFY] [InventoryTransaction.php](file:///d:/walet/gudang-walet/app/Models/InventoryTransaction.php)
- Add `supplier_id` to `$fillable`.
- Add `supplier()` relationship.

### Services
#### [MODIFY] [GradingGoodsService.php](file:///d:/walet/gudang-walet/app/Services/GradingGoods/GradingGoodsService.php)
- In `createInventoryFromGrading`, ensure `supplier_id` is saved.

#### [MODIFY] [TrackingStockService.php](file:///d:/walet/gudang-walet/app/Services/Stock/TrackingStockService.php)
- Update `getStockPerLocation`:
    - Select `supplier_id`.
    - Group by `location_id` AND `supplier_id`.
    - Return data including `supplier` info.
    - Handle `supplier_id` filter.
- Add `getAllSuppliers()` method.

### Controllers
#### [MODIFY] [TrackingStockController.php](file:///d:/walet/gudang-walet/app/Http/Controllers/Feature/TrackingStockController.php)
- Pass `supplier_id` filter and `suppliers` list to the view.

### Views
#### [MODIFY] [resources/views/admin/stock/detail.blade.php](file:///d:/walet/gudang-walet/resources/views/admin/stock/detail.blade.php)
- Add Supplier Filter.
- Add "Supplier" column to the table.

## Verification Plan

### Automated Tests
- Verify `InventoryTransaction` creation includes `supplier_id`.
- Verify `getStockPerLocation` groups correctly.

### Manual Verification
- Run migration and check database for backfilled `supplier_id`.
- Check Stock Detail page for Supplier breakdown.
