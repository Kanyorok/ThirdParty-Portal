<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    //
    public function index()
    {
        return view('inventory.item master.item category.index');
    }

    public function create(){
        return view('inventory.item master.item category.create');
    }
}
