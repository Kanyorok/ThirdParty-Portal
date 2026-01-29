<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class CapitalAdequacyController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.capitaladequacyratio.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.capitaladequacyratio.create');
    }
}
