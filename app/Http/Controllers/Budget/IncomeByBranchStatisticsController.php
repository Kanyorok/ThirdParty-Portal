<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class IncomeByBranchStatisticsController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.incomebybranch.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.incomebybranch.create');
    }
}
