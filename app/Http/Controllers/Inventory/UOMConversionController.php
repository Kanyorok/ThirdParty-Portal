<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UOMConversionController extends Controller
{
    //
    public function index()
    {
        return view('inventory.uom conversion.index');
    }

    public function create(){
        return view('inventory.uom conversion.create');
    }

}
