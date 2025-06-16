<?php

namespace App\Imports;

use App\Models\Inventory\LoadOpeningStock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OpeningStockImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new LoadOpeningStock([
            'BranchId'    => $row['branchid'],
            'StoreId'     => $row['storeid'],
            'ItemCode'    => $row['itemcode'],
            'Date' => Carbon::createFromFormat('d/m/Y', $row['date']),
            'Quantity'    => (int) $row['quantity'],
            'UOM'         => (int) $row['uom'],
            'Value'       => (float) $row['value'],
            'Remarks'     => $row['remarks'],
            'CreatedBy'   => auth()->id(),
            'CreatedOn'   => now(),
            'ModifiedBy'  => auth()->id(),
            'ModifiedOn'  => now(),
        ]);
    }
}

