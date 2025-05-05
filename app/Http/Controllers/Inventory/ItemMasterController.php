<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemMasterController extends Controller
{
    //
    public function index()
    {
        return view('inventory.item master.item master list.index');
    }

    public function create(){
        return view('inventory.item master.item master list.create');
    }
}
