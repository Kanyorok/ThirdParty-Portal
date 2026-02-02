<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class TransactionTypesController extends Controller
{
    public function index()
    {
        return view('finance.transactiontypes.index');
    }

    public function create()
    {
        return view('finance.transactiontypes.create');
    }
}
