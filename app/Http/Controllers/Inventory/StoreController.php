<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        $store = Store::all(); 
        return view('inventory.stores.index', compact('items'));
    }

    public function create(){
        return view('inventory.stores.create');
    }
}
