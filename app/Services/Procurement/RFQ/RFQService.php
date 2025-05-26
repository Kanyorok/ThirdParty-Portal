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
    public static function fetchRFQ(){

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
//    public static function RFQTOPO($rfqID){
//
//            return DB::table(DB::raw('t_RFQResponse WITH (NOLOCK)'))
//                ->leftJoin(DB::raw('t_ResponseItems WITH (NOLOCK)'), 't_RFQResponse.Id', '=', 't_ResponseItems.RfqResponseId')
////            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Orders.CreatedBy', '=', 't_Users.Id')
//                ->leftJoin(DB::raw('t_Suppliers WITH (NOLOCK)'), 't_RFQResponse.SupplierId', '=', 't_Suppliers.Id')
//                ->where('t_RFQResponse.RFQId', '=', $rfqID)
//                ->select(DB::raw('
//                t_ResponseItems.ItemName,
//                t_ResponseItems.Quantity,
//                t_ResponseItems.QuotedPrice,
//                t_ResponseItems.TotalPayable,
//                t_ResponseItems.UOM,
//                t_RFQResponse.RFQNumber,
//                t_RFQResponse.RFQResponseNumber,
//                t_RFQResponse.RFQId,
//                t_Suppliers.SupplierName
//            '))->first();
//        }

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
                't_Suppliers.SupplierName'
            )
            ->first();

        if (!$rfq) {
            return null;
        }

        // Get all items related to this RFQ response
        $items = DB::table('t_ResponseItems')
            ->where('RfqResponseId', $rfq->RFQResponseId)  // Use the actual response ID we got from the first query
            ->select(
                'ItemName',
                'Quantity',
                'QuotedPrice',
                'TotalPayable',
                'UOM'
            )
            ->get();

        // Convert items to array format if needed
        $rfq->items = $items->toArray();

        return $rfq;
    }
}
