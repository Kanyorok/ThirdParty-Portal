<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
