<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    //
    public function index()
    {
        return view('inventory.inventoryreports.index');
    }

    public function create(){
        return view('inventory.inventoryreports.create');
    }
}
