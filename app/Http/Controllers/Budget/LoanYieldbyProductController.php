<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class LoanYieldbyProductController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.loanyieldbyproduct.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.loanyieldbyproduct.create');
    }
}
