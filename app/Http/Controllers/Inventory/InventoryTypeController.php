<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryTypeController extends Controller
{
    //
    public function index()
    {
        return view('inventory.itemmaster.inventorytype.index');
    }

    public function create()
    {
        return view('inventory.itemmaster.inventorytype.create');
    }
}
