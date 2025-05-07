<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionReceiptsController extends Controller
{
    //
    public function index()
    {
        return view('inventory.transactions.receipts.index');
    }

    public function create(){
        return view('inventory.transactions.receipts.create');
    }
}
