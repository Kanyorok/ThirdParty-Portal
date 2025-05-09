<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemCategoryController extends Controller
{
    //
    public function index()
    {
        return view('inventory.itemmaster.itemcategory.index');
    }

    public function create(){
        return view('inventory.itemmaster.itemcategory.create');
    }
}
