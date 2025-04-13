<?php

namespace App\Services\ERP;


use App\Models\ERP\RequisitionLines;

class RequisitionItemService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function create(array $data): RequisitionLines
    {
        return  RequisitionLines::create([
        'Module' => $data['module'],
        'Item' => $data['item'],
        'Description' => $data['description'],
        'Quantity' => $data['quantity'],
        'UOM' => $data['uom'],
        'ExpectedPrice' => $data['expected_price'],
        'Urgency' => $data['urgency'],
        // optionally CreatedBy etc.
    ]);

    }
}
