<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionApprovalController extends Controller
{
    //
    public function index()
    {
        return view('inventory.transactions.transactionsapprovals.index');
    }

    public function create(){
        return view('inventory.transactions.transactionsapprovals.create');
    }
}
