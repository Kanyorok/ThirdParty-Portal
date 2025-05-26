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

    }
    public static function RFQTOPO($rfqID){

            return DB::table(DB::raw('t_RFQResponse WITH (NOLOCK)'))
                ->leftJoin(DB::raw('t_ResponseItems WITH (NOLOCK)'), 't_RFQResponse.Id', '=', 't_ResponseItems.RfqResponseId')
//            ->leftJoin(DB::raw('t_Users WITH (NOLOCK)'), 't_Orders.CreatedBy', '=', 't_Users.Id')
                ->leftJoin(DB::raw('t_Suppliers WITH (NOLOCK)'), 't_RFQResponse.SupplierId', '=', 't_Suppliers.Id')
                ->where('t_RFQResponse.RFQId', '=', $rfqID)
                ->select(DB::raw('
                t_ResponseItems.ItemName,
                t_ResponseItems.Quantity,
                t_ResponseItems.QuotedPrice,
                t_ResponseItems.TotalPayable,
                t_ResponseItems.UOM,
                t_RFQResponse.RFQNumber,
                t_RFQResponse.RFQResponseNumber,
                t_RFQResponse.RFQId,
                t_Suppliers.SupplierName
            '))->first();
        }
}
