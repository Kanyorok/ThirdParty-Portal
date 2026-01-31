<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class TaxSummaryReportController extends Controller
{
    public function index()
    {
        return view('finance.taxmanagement.taxsummaryreport.index');
    }

    public function create()
    {
        return view('finance.taxmanagement.taxsummaryreport.create');
    }
}
