<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BinTrackingController extends Controller
{
    //
    public function index()
    {
        return view('inventory.StockManagement.Bin-RackLocationTracking.index');
    }

    public function create(){
        return view('inventory.StockManagement.Bin-RackLocationTracking.create');
    }
}
