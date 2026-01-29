<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class BudgetVarianceAnalysisController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.monitoringandexecution.varianceanalysis.index');
    }

    public function create()
    {
        return view('budgetandanalytics.monitoringandexecution.varianceanalysis.create');
    }
}
