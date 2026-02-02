<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;

class StockIssueController extends Controller
{
    public function index()
    {
        return view('inventory.transactions.stockissue.index');
    }

    public function create()
    {
        return view('inventory.transactions.stockissue.create');
    }
}
