<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MovementDashboardController extends Controller
{
    //
    public function index()
    {
        return view('inventory.inventory dashboard.stock movement.index');
    }

    public function create(){
        return view('inventory.inventory dashboard.stock movement.create');
    }
}
