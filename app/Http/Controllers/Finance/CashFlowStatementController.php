<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class CashFlowStatementController extends Controller
{
    public function index()
    {
        return view('finance.financialreporting.cashflowstatement.index');
    }

    public function create()
    {
        return view('finance.financialreporting.cashflowstatement.create');
    }
}
