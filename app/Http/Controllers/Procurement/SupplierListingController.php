<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupplierListingController extends Controller
{
    //
    public function index()
    {
        return view('procurement.suppliers.supplierlist.index');
    }

    public function create(){
        return view('procurement.suppliers.supplierlist.create');
    }

}
