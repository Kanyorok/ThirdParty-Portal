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
                    ->select('t.Id', 't.ItemName', 't.ItemCode', 'i.LineItemID')
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

    public static function getItemDetails($item, $requisitionId = null)
    {
        if ($requisitionId) {
            $requisition = DB::table('t_Requisitions')
                ->select('PlanRef')
                ->where('Id', $requisitionId)
                ->first();

            if ($requisition && $requisition->PlanRef) {
                $planItem = DB::table('t_ConsolidatedProcurementPlan as pi')
                    ->join('t_PlanLineItem as i', 'pi.PlanID', '=', 'i.PlanID')
                    ->join('t_Items as t', 'i.ItemID', '=', 't.Id')
                    ->join('t_ItemTypes as f', 't.ItemType', '=', 'f.Id')
                    ->leftJoin('t_ItemCategories as c', 't.Category', '=', 'c.Id')
                    ->leftJoin('t_uom as u', 't.UOM', '=', 'u.Id')
                    ->where('pi.PlanID', $requisition->PlanRef)
                    ->where('t.Id', $item)
                    ->select([
                        't.ItemDescription',
                        DB::raw('u.Code AS UOM'),
                        DB::raw('u.Id AS UOMID'),
                        DB::raw('i.EstimatedUnitCost AS UnitPrice'),
                        'c.Name AS CategoryName',
                        'i.LineItemID AS LineItemID'
                    ])
                    ->get();

                // If item is found in the plan, return it
                if ($planItem) {
                    return $planItem;
                }
            }
        }

        // Fallback to direct t_Items fetch if no plan data or requisition
        return DB::table('t_Items')
            ->leftJoin('t_ItemCategories', 't_Items.Category', '=', 't_ItemCategories.Id')
            ->leftJoin('t_uom', 't_Items.UOM', '=', 't_uom.Id')
            ->leftJoin('t_Pricing', 't_Items.Id', '=', 't_Pricing.ItemID')
            ->leftJoin('t_ItemTypes', 't_Items.ItemType', '=', 't_ItemTypes.Id')
            ->where('t_Items.Id', $item)
            ->select([
                't_Items.ItemDescription',
                DB::raw('t_uom.Code AS UOM'),
                DB::raw('t_uom.Id AS UOMID'),
                DB::raw('t_Pricing.EstimatedPrice AS UnitPrice'),
                't_ItemCategories.Name AS CategoryName',
                DB::raw('NULL AS LineItemID')
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
