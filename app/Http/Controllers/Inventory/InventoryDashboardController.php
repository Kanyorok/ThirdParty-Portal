<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        return view('inventory.inventorydashboard.bybranchorstore.index');
    }

    public function create()
    {
        return view('inventory.inventorydashboard.bybranchorstore.create');
    }
}
