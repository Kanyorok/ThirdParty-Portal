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

            // Fetch stock item
            $stockItem = StockItem::where('ItemID', $data['ItemID'])
                ->where('Branch', $data['BranchID'])
                ->where('Store', $data['StoreID'])
                ->lockForUpdate()
                ->first();

            if (!$stockItem) {
                throw new \Exception('Stock item not found for specified Branch and Store.');
            }

            // Check stock availability
            if ($stockItem->CurrentQty < $data['Quantity']) {
                throw new \Exception('Insufficient stock available. Only ' . $stockItem->CurrentQty . ' units left.');
            }

            // Deduct stock
            $stockItem->CurrentQty -= $data['Quantity'];
            $stockItem->save();

            $stockConsumption = StockConsumption::create([
                'ConsumptionNo' => $consumptionNo,
                'ItemID' => $data['ItemID'],
                'BranchID' => $data['BranchID'],
                'StoreID' => $data['StoreID'],
                'IssuedToType' => $data['IssuedToType'],
                'IssuedToID' => $data['IssuedToID'],
                'Quantity' => $data['Quantity'],
                'UOM' => $data['UOM'],
                'IssuedBy' => $data['IssuedBy'],
                'IssuedOn' => $data['IssuedOn'],
                'Remarks' => $data['Remarks'] ?? null,
                'CreatedOn' => now(),
                'CreatedBy' => Auth::id(),
            ]);
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


            // Record stock transaction (OUT)
            StockTransaction::create([
                'SKUID' => $skuId,
                'TransactionType' => CodeDetail::where('CodeID', 'IssuedToType')->whereIn('Description', ['Employee', 'Department'])->value('ID'),
                'ReferenceID' => $stockConsumption->Id,
                'ItemID' => $data['ItemID'],
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


    protected function generateConsumptionNo(): string
    {
        $prefix = 'SC-' . date('ymd');

        $latest = StockConsumption::withTrashed() // Include soft deleted records
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

    public function update(StockConsumption $stockConsumption, array $data): StockConsumption
    {
        return DB::transaction(function () use ($stockConsumption, $data) {
            $stockConsumption->update([
                'ItemID' => $data['ItemID'],
                'BranchID' => $data['BranchID'],
                'StoreID' => $data['StoreID'],
                'IssuedToType' => $data['IssuedToType'],
                'IssuedToID' => $data['IssuedToID'],
                'Quantity' => $data['Quantity'],
                'UOM' => $data['UOM'],
                'IssuedBy' => $data['IssuedBy'],
                'IssuedOn' => $data['IssuedOn'],
                'Remarks' => $data['Remarks'] ?? null,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

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


}
