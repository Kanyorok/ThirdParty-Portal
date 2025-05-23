<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UOMController extends Controller
{
    //
        public function index()
    {
        return view('inventory.itemmaster.unitofmeasure.index');
    }

    public function create(){
        return view('inventory.itemmaster.unitofmeasure.create');
    }
    
    
}
