<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class LoanToDepositController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.loantodepositratio.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.loantodepositratio.create');
    }
}
