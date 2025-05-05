<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionTransfersController extends Controller
{
    //
    public function index()
    {
        return view('inventory.transactions.transfers.index');
    }

    public function create(){
        return view('inventory.transactions.transfers.create');
    }
}
