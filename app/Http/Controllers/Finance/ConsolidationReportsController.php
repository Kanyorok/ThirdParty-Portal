<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class ConsolidationReportsController extends Controller
{
    public function index()
    {
        return view('finance.financialreporting.consolidationreports.index');
    }

    public function create()
    {
        return view('finance.financialreporting.consolidationreports.create');
    }
}
