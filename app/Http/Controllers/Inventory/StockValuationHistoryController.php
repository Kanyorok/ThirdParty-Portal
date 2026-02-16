<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;

class StockValuationHistoryController extends Controller
{
    public function index()
    {
        return view('inventory.stockmanagement.stockvaluation.index');
    }

    public function create()
    {
        return view('inventory.stockmanagement.stockvaluation.create');
    }
}
