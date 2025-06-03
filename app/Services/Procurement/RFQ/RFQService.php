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
//            ->leftJoin(DB::raw('t_ResponseItems WITH (NOLOCK)'), 't_RFQResponse.Id', '=', 't_ResponseItems.RfqResponseId')
//            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Orders.CreatedBy', '=', 't_Users.Id')
            ->leftJoin(DB::raw('t_Suppliers WITH (NOLOCK)'), 't_RFQResponse.SupplierId', '=', 't_Suppliers.Id')
            ->select(DB::raw('
                t_RFQResponse.RFQNumber,
                t_RFQResponse.RFQResponseNumber,
                t_RFQResponse.RFQId,
                t_Suppliers.SupplierName
            '))->get();

    }

    public static function RFQTOPO($rfqID)
    {
        // Validate input
        if (!is_numeric($rfqID) || $rfqID <= 0) {
            throw new \InvalidArgumentException('Invalid RFQ ID');
        }

        // Get RFQ and supplier info
        $rfq = DB::table('t_RFQResponse')
            ->join('t_Suppliers', 't_RFQResponse.SupplierId', '=', 't_Suppliers.Id')
            ->where('t_RFQResponse.RFQId', $rfqID)
            ->select(
                't_RFQResponse.Id as RFQResponseId',
                't_RFQResponse.RFQNumber',
                't_RFQResponse.RFQResponseNumber',
                't_RFQResponse.RFQId',
                't_Suppliers.SupplierName',
                't_Suppliers.Id'
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
