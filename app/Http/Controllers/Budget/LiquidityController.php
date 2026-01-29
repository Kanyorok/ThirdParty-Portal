<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class LiquidityController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.liquidityratio.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.liquidityratio.create');
    }
}
