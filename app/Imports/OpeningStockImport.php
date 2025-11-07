<?php

namespace App\Imports;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\StockItem;
use App\Models\Inventory\Store;
use App\Models\Core\Branch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OpeningStockImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // ✅ Find Item
        $item = ItemMasterList::where('ItemCode', $row['itemcode'])->first();
        if (!$item) {
            \Log::warning("Item not found for ItemCode: {$row['itemcode']}");
            return null;
        }

        // ✅ Branch
        $branch = Branch::where('Name', $row['branchname'])->first();
        $branchId = $branch?->Id;

        // ✅ Store
        $store = Store::where('StoreName', $row['storename'])
            ->when($branchId, fn($q) => $q->where('BranchID', $branchId))
            ->first();
        $storeId = $store?->Id;

        if (!$branchId || !$storeId) {
            \Log::warning("Invalid Branch/Store for ItemCode: {$row['itemcode']} | Branch: {$row['branchname']} | Store: {$row['storename']}");
            return null;
        }

        // ✅ UOM (convert from Code to Id)
        $uom = \App\Models\Inventory\UnitOfMeasure::where('Code', $row['uom'])->first();
        $uomId = $uom?->Id;
        if (!$uomId) {
            \Log::warning("Invalid UOM for ItemCode: {$row['itemcode']} | UOM: {$row['uom']}");
        }

        // ✅ Price (convert from ActualPrice to Id)
        $price = PriceManagement::where('ActualPrice', $row['itemprice'])
            ->where('ItemID', $item->Id)
            ->first();
        $priceId = $price?->Id;
        if (!$priceId) {
            \Log::warning("Invalid Price for ItemCode: {$row['itemcode']} | Price: {$row['itemprice']}");
        }

        // ✅ SKUCode generation
        $codePart = Str::after($row['itemcode'], '-');
        $skuCode = 'SKU-' . '0' . $branchId . '-' . '0' . $storeId . '-' . $codePart;

        return new StockItem([
            'SKUCode' => $skuCode,
            'ItemID' => $item->Id,
            'Batch' => $row['batchtracked'] ?? false,
            'Serial' => $row['serialtracked'] ?? false,
            'Perishable' => $row['perishable'] ?? false,
            'Saleable' => $row['saleable'] ?? false,
            'Purchasable' => $row['purchasable'] ?? false,
            'Branch' => $branchId,
            'Store' => $storeId,
            'CurrentQty' => $row['qty'] ?? 0,
            'Min' => $row['minstocklevel'] ?? 0,
            'Reorder' => $row['reorderqty'] ?? 0,
            'Max' => $row['maxstocklevel'] ?? 0,
            'UOM' => $uomId,
            'UnitCost' => $priceId,
            'LastReceived' => !empty($row['lastreceiveddate'])
                ? Carbon::createFromFormat('d/m/Y', $row['lastreceiveddate'])
                : now(),
            'Status' => $row['isactive'] ?? true,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);
    }
}
