<?php

namespace App\Services\Inventory;

use App\Models\Core\Branch;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\LoadOpeningStock;
use App\Models\Inventory\Store;
use DateTime;
use Ramsey\Uuid\Type\Decimal;

class OpenStockService
{
    /**
     * Create a new class instance.
     */
    public function __construct( public LoadOpeningStock $loadopeningstock )
    {
    }
    public static function create(
        Branch $BranchId,
        Store $StoreId,
        ItemMasterList $ItemCode,
        DateTime $Date,
        Int $Quantity,
        ItemMasterList $UOM,
        Decimal $Value,
        String $Remarks, 
    ): self{
        $openstock = new LoadOpeningStock([
            'BranchId' => $BranchId,
            'StoreId' => $StoreId,
            'ItemCode' => $ItemCode,
            'Date' => $Date,
            'Quantity' => $Quantity,
            'UOM' => $UOM,
            'Value' => $Value,
            'CreatedBy' => auth()->user()->id,
            'ModifiedBy' => auth()->user()->id,
        ]);

        activity()->causedBy(auth()->user()->Id)->performedOn($openstock)->event('create')->log("Added Open stock entry {$openstock->Id}.");
        return new self($openstock); 
    }

}
