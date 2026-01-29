<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class BudgetLinesController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.budgetlinesmaster.index');
    }

    public function create()
    {
        return view('budgetandanalytics.budgetlinesmaster.create');
    }
}
