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
        return DB::table(DB::raw('t_Suppliers WITH (NOLOCK)'))
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 't_Suppliers.ThirdPartyID')
            ->select(
                DB::raw('COALESCE(tp.TradingName, t_Suppliers.SupplierName) as Name'),
                't_Suppliers.ContactEmail as Email',
                't_Suppliers.ContactPhone as Phone',
                DB::raw("COALESCE(tp.Address, t_Suppliers.Address, '') as Address"),
                't_Suppliers.CategoryId as CategoryId'
            )
            ->where('t_Suppliers.Id', $SupplierId)
            ->first(); // Return a single object, not a collection
    }
    public static function getSuppliers(){
        return DB::table(DB::raw('t_Suppliers WITH (NOLOCK)'))
            ->leftJoin('t_ThirdParties as tp', 'tp.Id', '=', 't_Suppliers.ThirdPartyID')
            ->select(
                DB::raw('COALESCE(tp.TradingName, t_Suppliers.SupplierName) as SupplierName'),
                't_Suppliers.ContactEmail as Email',
                't_Suppliers.ContactPhone as Phone',
                DB::raw("COALESCE(tp.Address, t_Suppliers.Address, '') as Address"),
                't_Suppliers.CategoryId as CategoryId',
                DB::raw('t_Suppliers.Id as SupplierId')
            )
            ->orderBy('SupplierName')
            ->get();
    }

}
