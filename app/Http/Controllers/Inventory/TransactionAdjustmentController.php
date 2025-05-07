<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionAdjustmentController extends Controller
{
    //
    public function index()
    {
        return view('inventory.transactions.adjustments.index');
    }

    public function create(){
        return view('inventory.transactions.adjustments.create');
    }
}
