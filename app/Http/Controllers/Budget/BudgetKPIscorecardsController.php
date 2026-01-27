<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class BudgetKPIscorecardsController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.monitoringandexecution.kpiscorecards.index');
    }

    public function create()
    {
        return view('budgetandanalytics.monitoringandexecution.kpiscorecards.create');
    }
}
