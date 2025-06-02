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
                    $skuCode = $this->generateSKUCode($data['Branch'], $data['Store']);
                    $data['SKUCode'] = $skuCode;

                    $stockItem = StockItem::create([
                        'ItemID'      => $data['ItemID'],
                        'Branch'      => $data['Branch'],
                        'Store'       => $data['Store'],
                        'SKUCode'     => $data['SKUCode'],
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
            $item->update([
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
                'ModifiedBy'  => auth()->id(),
                'ModifiedOn'  => now(),
            ]);

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

    protected function generateSKUCode(int $branchId, int $storeId): string
    {
        $lastSKU = StockItem::where('Branch', $branchId)
            ->where('Store', $storeId)
            ->orderByDesc('SKUCode')
            ->lockForUpdate()
            ->first();

        $newNumber = $lastSKU ? ((int) str_replace('SKU-', '', $lastSKU->SKUCode) + 1) : 1;
        return 'SKU-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    protected function isDuplicateSKUCodeError(QueryException $e): bool
    {
        return str_contains($e->getMessage(), 'Duplicate entry')
            && str_contains($e->getMessage(), 'sku_code_branch_store_unique');
    }
}
