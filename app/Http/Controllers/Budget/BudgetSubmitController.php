<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class BudgetSubmitController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.budgetworkspace.submitapproval.index');
    }

    public function create()
    {
        return view('budgetandanalytics.budgetworkspace.submitapproval.create');
    }
}
