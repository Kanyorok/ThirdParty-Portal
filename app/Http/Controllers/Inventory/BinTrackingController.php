<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;

class BinTrackingController extends Controller
{
    //
    public function index()
    {
        return view('inventory.stockmanagement.binracklocationtracking.index');
    }

    public function create(){
        return view('inventory.stockmanagement.binracklocationtracking.create');
    }
}
