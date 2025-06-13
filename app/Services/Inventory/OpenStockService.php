<?php

namespace App\Services\Inventory;

use App\Models\Auth\User;
use App\Models\Inventory\LoadOpeningStock;
use Carbon\Carbon;

class OpenStockService
{
    /**
     * Create a new class instance.
     */
    public function __construct( public LoadOpeningStock $loadopeningstock )
    {
    }
    public static function create(
        int $BranchId,
        int $StoreId,
        String $ItemCode,
        Carbon $Date,
        Int $Quantity,
        int $UOM,
        float $Value,
        String $Remarks,
        User $user 
    ): self{
        $openstock = new LoadOpeningStock([
            'BranchId' => $BranchId,
            'StoreId' => $StoreId,
            'ItemCode' => $ItemCode,
            'Date' => $Date,
            'Quantity' => $Quantity,
            'UOM' => $UOM,
            'Value' => $Value,
            'Remarks'=> $Remarks,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        $openstock->save();

        activity()->causedBy($user->Id)->performedOn($openstock)->event('create')->log("Added Open stock entry {$openstock->Id}.");
        return new self($openstock); 
    }

}
