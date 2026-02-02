<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;

class MovementDashboardController extends Controller
{
    public function index()
    {
        return view('inventory.inventorydashboard.stockmovement.index');
    }

    public function create()
    {
        return view('inventory.inventorydashboard.stockmovement.create');
    }
}
