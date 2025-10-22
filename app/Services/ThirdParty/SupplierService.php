<?php

namespace App\Services\ThirdParty;

use Illuminate\Support\Facades\DB;

class SupplierService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function getSupplierDetails($SupplierId){
        return DB::table(DB::raw('t_Suppliers AS s WITH (NOLOCK)'))
            ->join(DB::raw('t_ThirdParties AS tp WITH (NOLOCK)'), 'tp.Id', '=', 's.ThirdPartyID')
            ->select(
                DB::raw('tp.TradingName as Name'),
                DB::raw("COALESCE(tp.Email, '') as Email"),
                DB::raw("COALESCE(tp.Phone, '') as Phone"),
                DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                's.CategoryId as CategoryId'
            )
            ->where('s.Id', $SupplierId)
            ->first(); // Return a single object, not a collection
    }
    public static function getSuppliers(){
        return DB::table(DB::raw('t_Suppliers AS s WITH (NOLOCK)'))
            ->join(DB::raw('t_ThirdParties AS tp WITH (NOLOCK)'), 'tp.Id', '=', 's.ThirdPartyID')
            ->select(
                DB::raw('tp.TradingName as SupplierName'),
                DB::raw("COALESCE(tp.Email, '') as Email"),
                DB::raw("COALESCE(tp.Phone, '') as Phone"),
                DB::raw("COALESCE(tp.PhysicalAddress, '') as Address"),
                's.CategoryId as CategoryId',
                DB::raw('s.Id as SupplierId'),
                DB::raw('s.ThirdPartyID as ThirdPartyId')
            )
            ->whereNull('s.DeletedOn')
            ->orderBy('tp.TradingName', 'asc')
            ->get();
    }

}
