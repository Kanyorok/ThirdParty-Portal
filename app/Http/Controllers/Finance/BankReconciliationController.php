<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class BankReconciliationController extends Controller
{
    public function index()
    {
        // Display bank reconciliation dashboard
        return view('finance.bankreconciliation.index');
    }

    public function create()
    {
        // Show form for creating a new bank reconciliation entry
        return view('finance.bankreconciliation.create');
    }
}
