<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BankReconciliationController extends Controller
{
    public function index()
    {
        // Logic to display the bank reconciliation page
        return view('finance.bankreconciliation.index');
    }
    public function create()
    {
        // Logic to show the form for creating a new bank reconciliation
        return view('finance.bankreconciliation.create');
    }
}
