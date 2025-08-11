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
            ->select(
                't_Suppliers.SupplierName as Name',
                't_Suppliers.ContactEmail as Email',
                't_Suppliers.ContactPhone as Phone',
                't_Suppliers.Address as Address',
                't_Suppliers.CategoryId as CategoryId'
            )
            ->where('t_Suppliers.Id', $SupplierId)
            ->first(); // Return a single object, not a collection
    }
    public static function getSuppliers(){
        return DB::table(DB::raw('t_Suppliers WITH (NOLOCK)'))
            ->select(
                't_Suppliers.SupplierName as Name',
                't_Suppliers.ContactEmail as Email',
                't_Suppliers.ContactPhone as Phone',
                't_Suppliers.Address as Address',
                't_Suppliers.CategoryId as CategoryId',
                't_Suppliers.Id'
            )            ->get();
    }

}
