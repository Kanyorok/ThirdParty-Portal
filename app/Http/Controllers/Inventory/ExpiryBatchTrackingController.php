<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExpiryBatchTrackingController extends Controller
{
    //
    public function index()
    {
        return view('inventory.Stock Management.expiry batch tracking.index');
    }

    public function create(){
        return view('inventory.Stock Management.expiry batch tracking.create');
    }
}
