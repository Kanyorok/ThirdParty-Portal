<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OpeningStockController extends Controller
{
    //
    public function index()
    {
        return view('inventory.Stock Management.opening stock load.index');
    }

    public function create(){
        return view('inventory.Stock Management.opening stock load.create');
    }
}
