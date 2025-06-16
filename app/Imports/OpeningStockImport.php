<?php

namespace App\Imports;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\StockItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OpeningStockImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Match ItemCode to get Item ID
        $item = ItemMasterList::where('ItemCode', $row['itemcode'])->first();

        if (!$item) {
            \Log::warning("Item not found for ItemCode: {$row['itemcode']}");
            return null;
        }

        // SKUCode generation logic
        $codePart = Str::after($row['itemcode'], '-');
        $skuCode = 'SKU-'.'0'. $row['branchid'] . '-' .'0'. $row['storeid'] . '-'. $codePart;

        return new StockItem([
            'SKUCode'       => $skuCode,
            'ItemID'        => $item->Id,
            'Batch'         => $row['batchtracked'] ?? false,
            'Serial'        => $row['serialtracked'] ?? false,
            'Perishable'    => $row['perishable'] ?? false,
            'Saleable'      => $row['saleable'] ?? false,
            'Purchasable'   => $row['purchasable'],
            'Branch'        => $row['branchid'],
            'Store'         => $row['storeid'],
            'CurrentQty'    => $row['qty'],
            'Min'           => $row['minstocklevel'],
            'Reorder'       => $row['reorderqty'],
            'Max'           => $row['maxstocklevel'],
            'LastReceived'  => Carbon::createFromFormat('d/m/Y', $row['lastreceiveddate']),
            'Status'        => $row['isactive'] ?? true,
            'CreatedBy'     => auth()->id(),
            'CreatedOn'     => now(),
            'ModifiedBy'    => auth()->id(),
            'ModifiedOn'    => now(),
        ]);
    }
}
