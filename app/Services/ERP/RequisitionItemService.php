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

    public static function create(array $data, User $actor): RequisitionLines
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

    public static function getRequisitionItems(){

        return DB::table('t_RequisitionLines')
            ->select('t_RequisitionLines.*')
            ->get();
    }

    // public static function getItemDetails($item){
    //     // logger('Fetching items details: ' . $item);

    //     return DB::table('t_Items')
    //         ->join('t_ItemCategories','t_Items.CategoryId','=','t_ItemCategories.id')
    //         ->where('t_Items.id',$item)
    //         ->select('t_Items.Description','t_Items.UOM','t_Items.UnitPrice')
    //         ->get();
    // }


}
