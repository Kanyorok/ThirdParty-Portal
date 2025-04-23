<?php

namespace App\Services\ERP;

use App\Models\Procurement\RequisitionLines;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
        'CategoryId' => $data['CategoryId'],
        'CreatedBy' => $actor->Id,
        'ModifiedBy' => $actor->Id
        // optionally CreatedBy etc.
                                         ]);
    }

    public static function getRequisitionItems(){

        return DB::table(DB::raw('t_RequisitionLines WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_Items WITH (NOLOCK)'), 't_Items.Id', '=', 't_RequisitionLines.Item')
            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Users.Id', '=', 't_RequisitionLines.CreatedBy')
            ->select(
                't_RequisitionLines.*',
                't_Items.Name as ItemName',
                't_Users.Name as UserName',
                't_Items.UOM as UOMx',
                DB::raw('t_RequisitionLines.ExpectedPrice * t_RequisitionLines.Quantity as ExpectedPrice'),
                DB::raw('t_Items.UnitPrice * t_RequisitionLines.Quantity as ActualPrice'),
                DB::raw("CASE
            WHEN t_RequisitionLines.Status = 'p' THEN 'Pending'
            WHEN t_RequisitionLines.Status = 'a' THEN 'Approved'
            WHEN t_RequisitionLines.Status = 'r' THEN 'Rejected'
            ELSE 'Unknown'
        END as Status"),
                DB::raw("CASE
            WHEN t_RequisitionLines.Urgency = 1 THEN 'Very High'
            WHEN t_RequisitionLines.Urgency = 2 THEN 'High'
            WHEN t_RequisitionLines.Urgency = 3 THEN 'Medium'
            WHEN t_RequisitionLines.Urgency = 4 THEN 'Low'
            ELSE 'Unknown'
        END as Urgency"),
                DB::raw("FORMAT(t_RequisitionLines.CreatedOn, 'dd-MM-yyyy HH:mm') as CreatedOn")
                )
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
