<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class FinancialReportingController extends Controller
{
    public function index()
    {
        return view('finance.financialreporting.index');
    }

    public function create()
    {
        return view('finance.financialreporting.create');
    }
}
