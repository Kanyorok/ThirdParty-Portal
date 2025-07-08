<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StockIssueController extends Controller
{
    //
    public function index()
    {
        return view('inventory.transactions.stockissue.index');
    }

    public function create()
    {
        return view('inventory.transactions.stockissue.create');
    }
}
