<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class ReturnOnEquityController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.returnonequity.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.returnonequity.create');
    }
}
