<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class BudgetFormulaController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.formulasetup.index');
    }

    public function create()
    {
        return view('budgetandanalytics.formulasetup.create');
    }
}
