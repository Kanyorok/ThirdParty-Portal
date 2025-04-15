<?php

namespace App\Services\ERP;


use App\Models\ERP\RequisitionLines;
use App\Models\User;

class RequisitionItemService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function create(array $data,User $actor): RequisitionLines
    {
        return  RequisitionLines::create([
        'Module' => $data['Module'],
        'Type' => $data['Type'],
        'Item' => $data['Item'],
        'Description' => $data['Description'],
        'Quantity' => $data['Quantity'],
        'UOM' => $data['UOM'],
        'ExpectedPrice' => $data['ExpectedPrice'],
        'Urgency' => $data['Urgency'],
        'CreatedBy' => $actor->Id,
        'ModifiedBy' => $actor->Id
        // optionally CreatedBy etc.
    ]);

    }
}
