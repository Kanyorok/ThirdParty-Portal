<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SKUController extends Controller
{
    //
    public function index()
    {
        return view('inventory.item master.stock item.index');
    }

    public function create(){
        return view('inventory.item master.stock item.create');
    }
}
