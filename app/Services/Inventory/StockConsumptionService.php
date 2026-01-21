<?php

namespace App\Services\Inventory;

use App\Models\Inventory\StockConsumption;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
            $consumptionNo = $this->generateConsumptionNo();
            
            $stockItem = StockItem::where('Id', $data['ItemID'])
                ->where('Store', $data['StoreID'] ?? null)
                ->where('Branch', $data['BranchID'])
                ->first();

            if (!$stockItem) {
                throw new \Exception("Stock item not found for specified Branch and Store.");
            }

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

            $stockItem->CurrentQty -= $qty;
            $stockItem->save();

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
            $oldStockItem = StockItem::where('ItemID', $stockConsumption->ItemID)
                ->where('Store', $stockConsumption->StoreID)
                ->where('Branch', $stockConsumption->BranchID)
                ->first();

            if ($oldStockItem) {
                $oldStockItem->CurrentQty += $stockConsumption->Quantity;
                $oldStockItem->save();
            }

            $stockItem = StockItem::where('Id', $data['ItemID'])
                ->where('Store', $data['StoreID'])
                ->where('Branch', $data['BranchID'])
                ->first();

            if (!$stockItem) {
                throw new \Exception("Stock item not found for specified Branch and Store.");
            }

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

            $stockItem->CurrentQty -= $qty;
            $stockItem->save();

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
                    'UOMID'           => $data['UOM'],
                    'QuantityIn'      => 0,
                    'QuantityOut'     => $qty,
                    'BalanceQty'      => $stockItem->CurrentQty,
                    'TotalCost'       => ($stockItem->UnitCost ?? 0) * $qty,
                    'TransactionDate' => $data['IssuedOn'] ?? now(),
                    'Remarks'         => 'Stock consumed (Ref: ' . $stockConsumption->ConsumptionNo . ')',
                    'CreatedBy'       => Auth::id(),
                    'CreatedOn'       => now(),
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
            $stockItem = StockItem::where('ItemID', $stockConsumption->ItemID)
                ->where('Store', $stockConsumption->StoreID)
                ->where('Branch', $stockConsumption->BranchID)
                ->first();

            if ($stockItem) {
                $stockItem->CurrentQty += $stockConsumption->Quantity;
                $stockItem->save();
            }

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
