<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PriceManagementController extends Controller
{
    //

        public function index()
    {
        return view('inventory.pricemanagement.index');
    }

    public function create(){
        return view('inventory.pricemanagement.create');
    }
}
