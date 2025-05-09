<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OpeningStockController extends Controller
{
    //
    public function index()
    {
        return view('inventory.StockManagement.openingstockload.index');
    }

    public function create(){
        return view('inventory.StockManagement.openingstockload.create');
    }
}
