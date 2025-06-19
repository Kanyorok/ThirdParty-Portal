<?php
namespace App\Services\Procurement\Items;

use Illuminate\Support\Facades\DB;

class ItemService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
//
    }

    public static function getItemByType($type, $requisitionId = null)
    {
        if ($requisitionId) {
            $requisition = DB::table('t_Requisitions')->where('Id', $requisitionId)->first();

            if ($requisition && $requisition->PlanRef) {
                // Fetch from Consolidated Procurement Plan
                return DB::table('t_ConsolidatedProcurementPlan as pi')
                    ->join('t_PlanLineItem as i', 'pi.PlanID', '=', 'i.PlanID')
                    ->join('t_Items as t', 'i.ItemID', '=', 't.Id')
                    ->join('t_ItemTypes as f', 't.ItemType', '=', 'f.Id')
                    ->where('pi.PlanID', $requisition->PlanRef)
                    ->where('t.ItemType', $type)
                    ->select(
                        't.Id',
                        't.ItemName',
                        't.ItemCode',
                        'i.OriginalQty' // <-- Add OriginalQty from PlanLineItem
                    )
                    ->get();
            }
        }

        return DB::table('t_Items')
            ->leftJoin('t_ItemCategories', 't_Items.Category', '=', 't_ItemCategories.Id')
            ->leftJoin('t_ItemTypes', 't_Items.ItemType', '=', 't_ItemTypes.Id')
            ->where('t_ItemTypes.Id', $type)
            ->select(
                't_Items.Id',
                't_Items.ItemName',
                't_Items.ItemCode'
            )
            ->get();
    }

    public static function getItemDetails($itemId, $planId = null)
    {
        $query = DB::table('t_Items as t')
            ->leftJoin('t_ItemCategories as c', 't.Category', '=', 'c.Id')
            ->leftJoin('t_uom as u', 't.UOM', '=', 'u.Id');

        if ($planId) {
            $query->leftJoin('t_PlanLineItem as p', function ($join) use ($planId) {
                $join->on('p.ItemID', '=', 't.Id')
                    ->where('p.PlanID', '=', $planId);
            });
        }

        return $query->where('t.Id', $itemId)
            ->select([
                't.ItemDescription',
                DB::raw('u.Code AS UOM'),
                DB::raw('u.Id AS UOMID'),
                DB::raw('0 AS UnitPrice'),
                'c.Name AS CategoryName',
                DB::raw($planId ? 'ISNULL(p.OriginalQty, 0) AS OriginalQty' : '0 AS OriginalQty')
            ])
            ->get();
    }


    public static function getTypes()
    {
        // logger('Fetching items for type: ' . $type);

        return DB::table('t_ItemTypes')
            ->select('t_ItemTypes.Id', 't_ItemTypes.TypeName')
            ->where('Active', 1)
            ->whereNull('DeletedBy')
            ->wherenull('DeletedOn')
            ->get();
    }

}