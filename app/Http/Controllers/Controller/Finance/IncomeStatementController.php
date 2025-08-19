<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IncomeStatementController extends Controller
{
    public function index()
    {
        return view('finance.financialreporting.incomestatement.index');
    }

    public function create()
    {
        return view('finance.financialreporting.incomestatement.create');
    }
}
