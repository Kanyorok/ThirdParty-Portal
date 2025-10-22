<?php

namespace App\Services\Inventory;

use App\Models\Inventory\StockConsumption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\HRM\Department;
use App\Models\HRM\Employee;
use App\Models\Inventory\StockItem;
use App\Models\Core\CodeDetail;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\ItemMasterList;

class StockConsumptionService
{
    public function create(array $data): StockConsumption
    {
        return DB::transaction(function () use ($data) {
            $consumptionNo = $this->generateConsumptionNo();

            // Get the stock item
            $stockItem = StockItem::where('Id', $data['ItemID'])
                ->where('Store', $data['StoreID'] ?? null)
                ->where('Branch', $data['BranchID'])
                ->first();

            if (!$stockItem) {
                throw new \Exception("Stock item not found for specified Branch and Store.");
            }

            // Get the master item ID from the stock item
            $masterItemId = $stockItem->ItemID; // This should be the ID from t_Items

            if (!$masterItemId) {
                throw new \Exception("Master item reference not found for this stock item.");
            }

            $qty = (float)$data['Quantity'];
            if ($qty <= 0) {
                throw new \Exception("Quantity must be greater than zero.");
            }

            if ($stockItem->CurrentQty < $qty) {
                throw new \Exception("Insufficient stock available in branch. Only {$stockItem->CurrentQty} units left.");
            }

            // Update stock quantity
            $stockItem->CurrentQty -= $qty;
            $stockItem->save();

            // Create stock consumption record
            $stockConsumption = StockConsumption::create([
                'ConsumptionNo' => $consumptionNo,
                'ItemID' => $stockItem->Id, // Store StockItem ID
                'StoreID' => $data['StoreID'] ?? null,
                'BranchID' => $data['BranchID'],
                'UOM' => $data['UOM'],
                'Quantity' => $qty,
                'IssuedToType' => $data['IssuedToType'],
                'IssuedToID' => $data['IssuedToID'],
                'IssuedBy' => $data['IssuedBy'],
                'IssuedOn' => $data['IssuedOn'],
                'Remarks' => $data['Remarks'] ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
            ]);

            // Generate SKU ID
            $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                ->orderByDesc('id')
                ->value('SKUID');

            if ($latestSKU) {
                $number = (int)preg_replace('/[^0-9]/', '', $latestSKU);
                $nextNumber = str_pad($number + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $nextNumber = '001';
            }

            $skuId = 'SKU' . $nextNumber;

            // Get transaction type
            $transactionType = CodeDetail::where('CodeID', 'IssuedToType')
                ->whereIn('Description', ['Employee', 'Department'])
                ->value('ID');

            // Record stock transaction (OUT) - Use master item ID here
            StockTransaction::create([
                'SKUID' => $skuId,
                'TransactionType' => $transactionType,
                'ReferenceID' => $stockConsumption->Id,
                'ItemID' => $masterItemId, // Use the master item ID from t_Items
                'StoreID' => $data['StoreID'],
                'BranchID' => $data['BranchID'],
                'UnitCost' => $stockItem->UnitCost ?? 0,
                'UOMID' => $data['UOM'],
                'QuantityIn' => 0,
                'QuantityOut' => $data['Quantity'],
                'BalanceQty' => $stockItem->CurrentQty,
                'TotalCost' => ($stockItem->UnitCost ?? 0) * $data['Quantity'],
                'TransactionDate' => $data['IssuedOn'] ?? now(),
                'Remarks' => 'Stock consumed (Ref: ' . $consumptionNo . ')',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
            ]);

            activity()
                ->performedOn($stockConsumption)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Stock Consumption Recorded');

            return $stockConsumption;
        });
    }

    public function update(StockConsumption $stockConsumption, array $data): StockConsumption
    {
        return DB::transaction(function () use ($stockConsumption, $data) {
            // Revert old stock
            $oldStockItem = StockItem::where('Id', $stockConsumption->ItemID)
                ->where('Store', $stockConsumption->StoreID)
                ->where('Branch', $stockConsumption->BranchID)
                ->first();

            if ($oldStockItem) {
                $oldStockItem->CurrentQty += $stockConsumption->Quantity;
                $oldStockItem->save();
            }

            // Get new stock item
            $stockItem = StockItem::where('Id', $data['ItemID'])
                ->where('Store', $data['StoreID'])
                ->where('Branch', $data['BranchID'])
                ->first();

            if (!$stockItem) {
                throw new \Exception("Stock item not found for specified Branch and Store.");
            }

            // Get the master item ID
            $masterItemId = $stockItem->ItemID;

            if (!$masterItemId) {
                throw new \Exception("Master item reference not found for this stock item.");
            }

            if ($stockItem->CurrentQty < $data['Quantity']) {
                throw new \Exception("Insufficient stock available. Only {$stockItem->CurrentQty} left.");
            }

            // Update stock quantity
            $stockItem->CurrentQty -= $data['Quantity'];
            $stockItem->save();

            // Update consumption record
            $stockConsumption->update([
                'ItemID'        => $data['ItemID'],
                'BranchID'      => $data['BranchID'],
                'StoreID'       => $data['StoreID'],
                'IssuedToType'  => $data['IssuedToType'],
                'IssuedToID'    => $data['IssuedToID'],
                'Quantity'      => $data['Quantity'],
                'UOM'           => $data['UOM'],
                'IssuedBy'      => $data['IssuedBy'],
                'IssuedOn'      => $data['IssuedOn'],
                'Remarks'       => $data['Remarks'] ?? null,
                'ModifiedBy'    => Auth::id(),
                'ModifiedOn'    => now(),
            ]);

            // Update or create transaction
            $transaction = StockTransaction::where('ReferenceID', $stockConsumption->Id)
                ->where('TransactionType', CodeDetail::where('CodeID', 'IssuedToType')
                    ->whereIn('Description', ['Employee', 'Department'])
                    ->value('ID'))
                ->first();

            if ($transaction) {
                $transaction->update([
                    'ItemID' => $masterItemId, // Use master item ID
                    'StoreID' => $data['StoreID'],
                    'BranchID' => $data['BranchID'],
                    'UOMID' => $data['UOM'],
                    'QuantityOut' => $data['Quantity'],
                    'BalanceQty' => $stockItem->CurrentQty,
                    'TotalCost' => ($stockItem->UnitCost ?? 0) * $data['Quantity'],
                    'TransactionDate' => $data['IssuedOn'] ?? now(),
                    'Remarks' => 'Stock consumption updated (Ref: ' . $stockConsumption->ConsumptionNo . ')',
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);
            } else {
                // Create new transaction if not exists
                $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                    ->orderByDesc('id')
                    ->value('SKUID');

                $nextNumber = $latestSKU
                    ? str_pad(((int)preg_replace('/[^0-9]/', '', $latestSKU)) + 1, 3, '0', STR_PAD_LEFT)
                    : '001';

                $skuId = 'SKU' . $nextNumber;

                $transactionType = CodeDetail::where('CodeID', 'IssuedToType')
                    ->whereIn('Description', ['Employee', 'Department'])
                    ->value('ID');

                StockTransaction::create([
                    'SKUID' => $skuId,
                    'TransactionType' => $transactionType,
                    'ReferenceID' => $stockConsumption->Id,
                    'ItemID' => $masterItemId, // Use master item ID
                    'StoreID' => $data['StoreID'],
                    'BranchID' => $data['BranchID'],
                    'UnitCost' => $stockItem->UnitCost ?? 0,
                    'UOMID' => $data['UOM'],
                    'QuantityIn' => 0,
                    'QuantityOut' => $data['Quantity'],
                    'BalanceQty' => $stockItem->CurrentQty,
                    'TotalCost' => ($stockItem->UnitCost ?? 0) * $data['Quantity'],
                    'TransactionDate' => $data['IssuedOn'] ?? now(),
                    'Remarks' => 'Stock consumed (Ref: ' . $stockConsumption->ConsumptionNo . ')',
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                ]);
            }

            activity()
                ->performedOn($stockConsumption)
                ->causedBy(Auth::user())
                ->withProperties(['attributes' => $data])
                ->log('Stock Consumption Updated');

            return $stockConsumption;
        });
    }

    public function delete(StockConsumption $stockConsumption): bool
    {
        return DB::transaction(function () use ($stockConsumption) {
            $stockConsumption->DeletedBy = Auth::id();
            $stockConsumption->save();
            $stockConsumption->delete();

            activity()
                ->performedOn($stockConsumption)
                ->causedBy(Auth::user())
                ->log('Stock Consumption Deleted');

            return true;
        });
    }

    protected function generateConsumptionNo(): string
    {
        $prefix = 'SC-' . date('ymd');
        $latest = StockConsumption::withTrashed()
            ->where('ConsumptionNo', 'LIKE', "$prefix%")
            ->orderBy('ConsumptionNo', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int)substr($latest->ConsumptionNo, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }
}
