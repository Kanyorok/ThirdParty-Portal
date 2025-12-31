<?php

namespace App\Services\Inventory;

use App\Models\Inventory\StockConsumption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\HRM\Department;
use App\Models\HRM\Employee;
use App\Models\Inventory\StockItem;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\StockTransaction;
use App\Models\Inventory\ItemMasterList;

class StockConsumptionService
{
    public function create(array $data): StockConsumption
    {
        return DB::transaction(function () use ($data) {
            Log::info('Stock Consumption Create Data:', $data);
            
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
            $masterItemId = $stockItem->ItemID;

            if (!$masterItemId) {
                throw new \Exception("Master item reference not found for this stock item.");
            }

            $qty = (float) $data['Quantity'];
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
                'ItemID' => $stockItem->ItemID,
                'StoreID'       => $data['StoreID'] ?? null,
                'BranchID'      => $data['BranchID'],
                'UOM'           => $data['UOM'],
                'Quantity'      => $qty,
                'IssuedToType'  => $data['IssuedToType'],
                'IssuedToID'    => $data['IssuedToID'],
                'IssuedBy'      => $data['IssuedBy'],
                'IssuedOn'      => $data['IssuedOn'],
                'Remarks'       => $data['Remarks'] ?? null,
                'CreatedBy'     => Auth::id(),
                'ModifiedBy'    => Auth::id(),
            ]);

            // Generate SKU ID
            $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                ->orderByDesc('id')
                ->value('SKUID');

            if ($latestSKU) {
                $number = (int) preg_replace('/[^0-9]/', '', $latestSKU);
                $nextNumber = str_pad($number + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $nextNumber = '001';
            }

            $skuId = 'SKU' . $nextNumber;

            // Get transaction type based on IssuedToType
            $transactionType = $this->getTransactionType($data['IssuedToType']);

            // Record stock transaction (OUT)
            StockTransaction::create([
                'SKUID'           => $skuId,
                'TransactionType' => $transactionType,
                'ReferenceID'     => $stockConsumption->Id,
                'ItemID'          => $masterItemId,
                'StoreID'         => $data['StoreID'],
                'BranchID'        => $data['BranchID'],
                'UnitCost'        => $stockItem->UnitCost ?? 0,
                'UOMID'           => $data['UOM'],
                'QuantityIn'      => 0,
                'QuantityOut'     => $data['Quantity'],
                'BalanceQty'      => $stockItem->CurrentQty,
                'TotalCost'       => ($stockItem->UnitCost ?? 0) * $data['Quantity'],
                'TransactionDate' => $data['IssuedOn'] ?? now(),
                'Remarks'         => 'Stock consumed (Ref: ' . $consumptionNo . ')',
                'CreatedBy'       => Auth::id(),
                'CreatedOn'       => now(),
            ]);

            // Log activity
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
            Log::info('Stock Consumption Update Data:', $data);
            
            // Revert old stock
            $oldStockItem = StockItem::where('ItemID', $stockConsumption->ItemID)
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

            $qty = (float) $data['Quantity'];
            if ($qty <= 0) {
                throw new \Exception("Quantity must be greater than zero.");
            }

            if ($stockItem->CurrentQty < $qty) {
                throw new \Exception("Insufficient stock available. Only {$stockItem->CurrentQty} left.");
            }

            // Update stock quantity
            $stockItem->CurrentQty -= $qty;
            $stockItem->save();

            // Update consumption record
            $stockConsumption->update([
                'ItemID'        => $masterItemId,
                'StoreID'       => $data['StoreID'],
                'BranchID'      => $data['BranchID'],
                'IssuedToType'  => $data['IssuedToType'],
                'IssuedToID'    => $data['IssuedToID'],
                'Quantity'      => $qty,
                'UOM'           => $data['UOM'],
                'IssuedBy'      => $data['IssuedBy'],
                'IssuedOn'      => $data['IssuedOn'],
                'Remarks'       => $data['Remarks'] ?? null,
                'ModifiedBy'    => Auth::id(),
                'ModifiedOn'    => now(),
            ]);

            // Update or create transaction
            $transaction = StockTransaction::where('ReferenceID', $stockConsumption->Id)
                ->first();

            if ($transaction) {
                $transaction->update([
                    'ItemID'          => $masterItemId,
                    'StoreID'         => $data['StoreID'],
                    'BranchID'        => $data['BranchID'],
                    'UOMID'           => $data['UOM'],
                    'QuantityOut'     => $qty,
                    'BalanceQty'      => $stockItem->CurrentQty,
                    'TotalCost'       => ($stockItem->UnitCost ?? 0) * $qty,
                    'TransactionDate' => $data['IssuedOn'] ?? now(),
                    'Remarks'         => 'Stock consumption updated (Ref: ' . $stockConsumption->ConsumptionNo . ')',
                    'ModifiedBy'      => Auth::id(),
                    'ModifiedOn'      => now(),
                ]);
            } else {
                // Create new transaction if not exists
                $latestSKU = StockTransaction::where('SKUID', 'like', 'SKU%')
                    ->orderByDesc('id')
                    ->value('SKUID');

                $nextNumber = $latestSKU
                    ? str_pad(((int) preg_replace('/[^0-9]/', '', $latestSKU)) + 1, 3, '0', STR_PAD_LEFT)
                    : '001';

                $skuId = 'SKU' . $nextNumber;
                $transactionType = $this->getTransactionType($data['IssuedToType']);

                StockTransaction::create([
                    'SKUID'           => $skuId,
                    'TransactionType' => $transactionType,
                    'ReferenceID'     => $stockConsumption->Id,
                    'ItemID'          => $masterItemId,
                    'StoreID'         => $data['StoreID'],
                    'BranchID'        => $data['BranchID'],
                    'UnitCost'        => $stockItem->UnitCost ?? 0,
                    'UOMID'          => $data['UOM'],
                    'QuantityIn'     => 0,
                    'QuantityOut'    => $qty,
                    'BalanceQty'     => $stockItem->CurrentQty,
                    'TotalCost'      => ($stockItem->UnitCost ?? 0) * $qty,
                    'TransactionDate'=> $data['IssuedOn'] ?? now(),
                    'Remarks'        => 'Stock consumed (Ref: ' . $stockConsumption->ConsumptionNo . ')',
                    'CreatedBy'      => Auth::id(),
                    'CreatedOn'      => now(),
                ]);
            }

            // Log activity
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
            // Revert stock before deletion
            $stockItem = StockItem::where('ItemID', $stockConsumption->ItemID)
                ->where('Store', $stockConsumption->StoreID)
                ->where('Branch', $stockConsumption->BranchID)
                ->first();

            if ($stockItem) {
                $stockItem->CurrentQty += $stockConsumption->Quantity;
                $stockItem->save();
            }

            // Soft delete the consumption
            $stockConsumption->DeletedBy = Auth::id();
            $stockConsumption->save();
            $stockConsumption->delete();

            // Log activity
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
            $lastNumber = (int) substr($latest->ConsumptionNo, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    protected function getTransactionType($issuedToTypeId)
    {
        $typeDetail = CodeDetail::find($issuedToTypeId);
        if (!$typeDetail) {
            return null;
        }

        $typeName = strtoupper($typeDetail->Description);
        
        // Map consumption type to transaction type
        if ($typeName === 'EMPLOYEE') {
            return CodeDetail::where('CodeID', 'TransactionType')
                ->where('Description', 'like', '%Employee%')
                ->value('ID');
        } elseif ($typeName === 'DEPARTMENT') {
            return CodeDetail::where('CodeID', 'TransactionType')
                ->where('Description', 'like', '%Department%')
                ->value('ID');
        }
        
        return null;
    }
}