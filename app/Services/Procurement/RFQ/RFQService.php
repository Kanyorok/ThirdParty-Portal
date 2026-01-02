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
        //
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
        if (!is_numeric($rfqID) || $rfqID <= 0) {
            throw new \InvalidArgumentException('Invalid RFQ ID');
        }

        // Get RFQ and supplier info
        $rfq = DB::table('t_RFQResponse')
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 't_RFQResponse.SupplierId')
            ->where('t_RFQResponse.RFQId', $rfqID)
            ->select(
                't_RFQResponse.Id as RFQResponseId',
                't_RFQResponse.RFQNumber',
                't_RFQResponse.RFQResponseNumber',
                't_RFQResponse.RFQId',
                DB::raw("COALESCE(tp.TradingName, '') as SupplierName"),
                DB::raw('tp.Id as Id')
            )
            ->first();

        if (!$rfq) {
            return null;
        }

        // Get all items related to this RFQ response
        $items = DB::table('t_ResponseItems')
            ->leftJoin('t_RFQResponse', 't_ResponseItems.RfqResponseId', '=', 't_RFQResponse.Id')
            ->leftJoin('t_RFQ', 't_RFQResponse.RFQId', '=', 't_RFQ.Id')
            ->leftJoin('t_RFQLines', 't_RFQ.Id', '=', 't_RFQLines.RFQId')
            ->leftJoin('t_Items', 't_RFQLines.ItemId', '=', 't_Items.Id')
            ->where('t_ResponseItems.RfqResponseId', $rfq->RFQResponseId)
            ->whereNotNull('t_Items.Id') // prevent missing item records
            ->select(
                't_Items.ItemName',
                't_RFQLines.Quantity',
                't_ResponseItems.QuotedPrice',
                't_ResponseItems.TotalPayable',
                't_Items.UOM',
                't_Items.ItemType',
                't_Items.ItemDescription',
                't_Items.Id'
            )
            ->get();

        // Convert items to array format if needed
        $rfq->items = $items->toArray();

        return $rfq;
    }
}
