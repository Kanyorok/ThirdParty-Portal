<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class CostToIncomeController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.costtoincomeratio.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.costtoincomeratio.create');
    }
}
