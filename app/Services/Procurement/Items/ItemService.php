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
                        'i.LineItemID',
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

  public static function getItemDetails($itemId, $requisitionId = null, $planId = null)
  {
      // Check if requisition ID is provided and try to get the PlanRef
      if ($requisitionId) {
          $requisition = DB::table('t_Requisitions')
              ->select('PlanRef')
              ->where('Id', $requisitionId)
              ->first();

          if ($requisition && $requisition->PlanRef) {
              $planId = $requisition->PlanRef;
          }
      }

      // If plan ID is present, attempt to get item details from plan
      if ($planId) {
          $planItem = DB::table('t_ConsolidatedProcurementPlan as pi')
              ->join('t_PlanLineItem as i', function ($join) use ($planId) {
                  $join->on('pi.PlanID', '=', 'i.PlanID')
                       ->where('pi.PlanID', '=', $planId);
              })
              ->join('t_Items as t', 'i.ItemID', '=', 't.Id')
              ->join('t_ItemTypes as f', 't.ItemType', '=', 'f.Id')
              ->leftJoin('t_ItemCategories as c', 't.Category', '=', 'c.Id')
              ->leftJoin('t_uom as u', 't.UOM', '=', 'u.Id')
              ->where('t.Id', $itemId)
              ->select([
                  't.ItemDescription',
                  DB::raw('u.Code AS UOM'),
                  DB::raw('u.Id AS UOMID'),
                  DB::raw('i.EstimatedUnitCost AS UnitPrice'),
                  'c.Name AS CategoryName',
                  'i.LineItemID AS LineItemID',
                  DB::raw('ISNULL(i.OriginalQty, 0) AS OriginalQty')
              ])
              ->get();

          if ($planItem && $planItem->isNotEmpty()) {
              return $planItem;
          }
      }

      // Fallback to direct item fetch if no plan item found
      return DB::table('t_Items as t')
          ->leftJoin('t_ItemCategories as c', 't.Category', '=', 'c.Id')
          ->leftJoin('t_uom as u', 't.UOM', '=', 'u.Id')
          ->leftJoin('t_Pricing as p', 't.Id', '=', 'p.ItemID')
          ->leftJoin('t_ItemTypes as f', 't.ItemType', '=', 'f.Id')
          ->where('t.Id', $itemId)
          ->select([
              't.ItemDescription',
              DB::raw('u.Code AS UOM'),
              DB::raw('u.Id AS UOMID'),
              DB::raw('p.EstimatedPrice AS UnitPrice'),
              'c.Name AS CategoryName',
              DB::raw('NULL AS LineItemID'),
              DB::raw('0 AS OriginalQty')
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