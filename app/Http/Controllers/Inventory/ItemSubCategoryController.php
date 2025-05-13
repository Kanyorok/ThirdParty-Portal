<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemSubCategoryController extends Controller
{
    //
    public function index()
    {
        return view('inventory.itemmaster.itemsubcategory.index');
    }

    public function create(){
        return view('inventory.itemmaster.itemsubcategory.create');
    }
}
