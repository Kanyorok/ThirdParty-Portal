<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockTakeController extends Controller
{
    //
    public function index()
    {
        return view('inventory.StockManagement.stocktake.index');
    }

    public function create(){
        return view('inventory.StockManagement.stocktake.create');
    }
}
