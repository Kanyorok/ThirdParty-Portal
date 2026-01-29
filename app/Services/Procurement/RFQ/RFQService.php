<?php

namespace App\Services\Procurement\RFQ;

use Illuminate\Support\Facades\DB;

class RFQService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
    }

    public static function fetchRFQ()
    {

        return DB::table(DB::raw('t_RFQResponse WITH (NOLOCK)'))
            ->leftJoin(DB::raw('t_ThirdParties WITH (NOLOCK)'), 't_ThirdParties.Id', '=', 't_RFQResponse.SupplierId')
            ->select(DB::raw("\n                t_RFQResponse.RFQNumber,\n                t_RFQResponse.RFQResponseNumber,\n                t_RFQResponse.RFQId,\n                t_ThirdParties.Id as SupplierId,\n                t_ThirdParties.TradingName as TradingName,\n                t_ThirdParties.PhysicalAddress as Address\n            "))->get();
    }

    public static function RFQTOPO($rfqID)
    {
        // Validate input
        if (! is_numeric($rfqID) || $rfqID <= 0) {
            throw new \InvalidArgumentException('Invalid RFQ ID');
        }

        // 1. First, try to find the AWARDED response for this RFQ
        // We join t_RFQAward to find the specific supplier who won
        $award = DB::table('t_RFQAward')
            ->where('RFQId', $rfqID)
            ->first();

        // Base query for response
        $query = DB::table('t_RFQResponse')
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 't_RFQResponse.SupplierId')
            ->where('t_RFQResponse.RFQId', $rfqID)
            ->select(
                't_RFQResponse.Id as RFQResponseId',
                't_RFQResponse.RFQNumber',
                't_RFQResponse.RFQResponseNumber',
                't_RFQResponse.RFQId',
                DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                DB::raw('tp.Id as Id') // Supplier/ThirdParty ID
            );

        // Filter by awarded supplier if found
        if ($award) {
            // Finding the response for the winning supplier
            // note: t_RFQAward.SupplierId points to t_Suppliers, but t_RFQResponse.SupplierId points to t_ThirdParties directly?
            // Let's verify via join: t_Suppliers -> t_SupplierMaster -> t_ThirdParties or t_Suppliers -> t_ThirdParties
            // Based on previous fixes: t_RFQAward.SupplierID -> t_Suppliers.ID.
            // t_Suppliers has link to t_SupplierMaster or ThirdParty.
            // Let's assume we filter by the match in RFQResponse (which usually holds ThirdPartyID in SupplierId column based on typical schema).

            // Wait, t_RFQResponse.SupplierId usually stores the ThirdPartyID directly in many systems, OR the SupplierID.
            // The previous code joined t_ThirdParties ON t_RFQResponse.SupplierId = t_ThirdParties.Id.
            // So t_RFQResponse.SupplierId IS a ThirdPartyID.

            // effectively, we need the ThirdPartyID of the awarded supplier.
            $winningThirdPartyId = DB::table('t_Suppliers as s')
                ->join('t_SupplierMaster as sm', 's.SupplierMasterId', '=', 'sm.Id')
                ->where('s.Id', $award->SupplierId)
                ->value('sm.ThirdPartyId');

            if ($winningThirdPartyId) {
                $query->where('t_RFQResponse.SupplierId', $winningThirdPartyId);
            }
        }

        $rfq = $query->first();

        if (! $rfq) {
            // Fallback: Just get the first response (logic from before, but risky)
            return null;
        }

        // Get all items related to this SPECIFIC RFQ response
        // User provided schema for t_RFQLines showing it has ItemId and ItemName.
        // t_ResponseItems has ItemName but no ItemId or RFQLineId.
        // We link ResponseItems -> RFQLines (via Name) -> Items (via ItemId)
        // We must ensure we match the correct RFQ via RFQId to avoid name collisions from other RFQs.

        $items = DB::table('t_ResponseItems')
             ->leftJoin('t_RFQResponse', 't_ResponseItems.RfqResponseId', '=', 't_RFQResponse.Id')
             ->leftJoin('t_RFQLines', function ($join) use ($rfq) {
                 $join->on('t_ResponseItems.ItemName', '=', 't_RFQLines.ItemName')
                      ->where('t_RFQLines.RFQId', '=', $rfq->RFQId);
             })
             ->leftJoin('t_Items', 't_RFQLines.ItemId', '=', 't_Items.Id')
             ->where('t_ResponseItems.RfqResponseId', $rfq->RFQResponseId)
             ->select(
                 't_ResponseItems.ItemName', // Use response name or resolved name
                 't_ResponseItems.Quantity', // Use quoted quantity
                 't_ResponseItems.QuotedPrice', // Use actual quoted price
                 't_ResponseItems.TotalPayable',
                 't_Items.UOM',
                 't_Items.ItemType',
                 't_Items.ItemDescription',
                 't_Items.Id' // Resolves correctly via t_RFQLines -> t_Items
             )
             ->get();

        // If items are still empty (e.g. name mismatch), we return empty to avoid errors
        // or one could try a direct name match to t_Items as a last resort, but t_RFQLines is safer.

        $rfq->items = $items->toArray();

        return $rfq;
    }
}
