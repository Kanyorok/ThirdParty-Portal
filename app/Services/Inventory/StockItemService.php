<?php
 
namespace App\Services\Inventory;
 
use App\Models\Inventory\StockItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
 
class StockItemService
{
    public function create(array $data): string
    {
        $maxRetries = 3;
        $attempt = 0;
 
        do {
            try {
                return DB::transaction(function () use ($data) {
                    // Create StockItem first to get primary key `Id`
                    $stockItem = StockItem::create([
                        'ItemID'      => $data['ItemID'],
                        'Branch'      => $data['Branch'],
                        'Store'       => $data['Store'],
                        'CurrentQty'  => $data['CurrentQty'],
                        'Status'      => $data['Status'],
                        'Batch'       => $data['Batch'] ?? false,
                        'Serial'      => $data['Serial'] ?? false,
                        'Perishable'  => $data['Perishable'] ?? false,
                        'Saleable'    => $data['Saleable'] ?? false,
                        'Purchasable' => $data['Purchasable'] ?? false,
                        'Min'         => $data['Min'] ?? 0,
                        'Reorder'     => $data['Reorder'] ?? 0,
                        'Max'         => $data['Max'] ?? 0,
                        'LastReceived'=> $data['LastReceived'] ?? null,
                        'CreatedBy'   => auth()->id(),
                        'CreatedOn'   => now(),
                        'ModifiedBy'  => auth()->id(),
                        'ModifiedOn'  => now(),
                    ]);
 
                    // Generate SKU based on `Id`, Branch, Store
                    $skuCode = $this->generateSKUCode($stockItem->Id, $data['Branch'], $data['Store']);
                    $stockItem->update(['SKUCode' => $skuCode]);
 
                    // Log activity
                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($stockItem)
                        ->event('created')
                        ->log('Created Stock Item with SKUCode ' . $stockItem->SKUCode);
 
                    return $stockItem->SKUCode;
                });
            } catch (QueryException $e) {
                if ($this->isDuplicateSKUCodeError($e)) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        throw new \Exception('Unable to generate unique SKUCode after multiple attempts.');
                    }
                } else {
                    throw $e;
                }
            }
        } while ($attempt < $maxRetries);
    }
 
    public function update(StockItem $item, array $data): void
    {
        DB::transaction(function () use ($item, $data) {
            // Regenerate SKU when branch or store changes
            if ($data['Branch'] !== $item->Branch || $data['Store'] !== $item->Store) {
                $data['SKUCode'] = $this->generateSKUCode($item->Id, $data['Branch'], $data['Store']);
            }
 
            $item->update($data);
 
            activity()
                ->causedBy(auth()->user())
                ->performedOn($item)
                ->event('updated')
                ->log('Updated Stock Item with SKUCode ' . $item->SKUCode);
        });
    }
 
    public function delete(StockItem $item): void
    {
        DB::transaction(function () use ($item) {
            $skuCode = $item->SKUCode;
            $item->delete();
 
            activity()
                ->causedBy(auth()->user())
                ->performedOn($item)
                ->event('deleted')
                ->log('Deleted Stock Item with SKUCode ' . $skuCode);
        });
    }
 
    /**
     * Generate SKUCode based on Item ID, Branch, and Store
     */
    protected function generateSKUCode(int $Id, int $branchId, int $storeId): string
    {
        return 'SKU-' . str_pad($branchId, 2, '0', STR_PAD_LEFT) . '-' .
                       str_pad($storeId, 2, '0', STR_PAD_LEFT) . '-' .
                       str_pad($Id, 5, '0', STR_PAD_LEFT);
    }
 
    /**
     * Check Duplicate SKUCode
     */
    protected function isDuplicateSKUCodeError(QueryException $e): bool
    {
        return str_contains($e->getMessage(), 'Duplicate entry')
            || str_contains($e->getMessage(), 'Cannot insert duplicate key')
            || str_contains($e->getMessage(), 'sku_code_branch_store_unique');
    }
}