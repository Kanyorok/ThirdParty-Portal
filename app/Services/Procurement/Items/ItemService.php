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
            ->where('t_ItemTypes.Id', $type)
            ->select('t_Items.Id','t_Items.ItemName','t_Items.ItemCode')
            ->get();
    }

    public static function getItemDetails($item)
    {
        return DB::table('t_Items')
            ->leftJoin('t_ItemCategories', 't_Items.Category', '=', 't_ItemCategories.Id')
            ->leftJoin('t_uom', 't_Items.UOM', '=', 't_uom.Id')
            ->where('t_Items.Id', $item)
            ->select([
                't_Items.ItemDescription',
                DB::raw('t_uom.Code AS UOM'),
                DB::raw('t_uom.Id AS UOMID'),
                DB::raw('0 AS UnitPrice'),
                't_ItemCategories.Name AS CategoryName'
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
