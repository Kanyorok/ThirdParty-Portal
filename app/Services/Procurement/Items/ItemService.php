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
        // logger('Fetching items for type: ' . $type);

        if ($requisitionId) {
            $requisition = DB::table('t_Requisitions')->where('Id', $requisitionId)->first();

            if ($requisition && $requisition->PlanRef) {
                // Fetch from Consolidate Procurement Plan
                return DB::table('t_ConsolidatedProcurementPlan as pi')
                    ->join('t_PlanLineItem as i', 'pi.PlanID', '=', 'i.PlanID')
                    ->join('t_Items as t', 'i.ItemID', '=', 't.Id')
                    ->join('t_ItemTypes as f', 't.ItemType', '=', 'f.Id')
                    ->where('pi.PlanID', $requisition->PlanRef)
                    ->where('t.ItemType', $type)
                    ->select('t.Id', 't.ItemName', 't.ItemCode')
                    ->get();
            }
        }

        return DB::table('t_Items')
            ->leftjoin('t_ItemCategories', 't_Items.Category', '=', 't_ItemCategories.Id')
            ->leftjoin('t_ItemTypes', 't_Items.ItemType', '=', 't_ItemTypes.Id')
            ->leftjoin('t_Pricing', 't_Items.Id', '=', 't_Pricing.ItemID')
            ->where('t_ItemTypes.Id', $type)
            ->select('t_Items.Id','t_Items.ItemName','t_Items.ItemCode', 't_Pricing.EstimatedPrice')
            ->get();
    }

    public static function getItemDetails($item)
    {
        return DB::table('t_Items as items')
            ->leftJoin('t_PlanLineItem as plan', 'items.Id', '=', 'plan.ItemID')
            ->leftJoin('t_Pricing as price', 'items.Id', '=', 'price.ItemID')
            ->leftJoin('t_ItemCategories as cat', 'items.Category', '=', 'cat.Id')
            ->leftJoin('t_uom as uom', 'items.UOM', '=', 'uom.Id')
            ->where('items.Id', $item)
            ->select([
                'items.ItemDescription',
                DB::raw('COALESCE(uom.Code, "") AS UOM'),
                DB::raw('uom.Id AS UOMID'),
                DB::raw('COALESCE(price.EstimatedPrice, plan.EstimatedUnitCost, 0) AS UnitPrice'),
                'cat.Name AS CategoryName'
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
