<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetConsolidationController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.budgetworkspace.budgetconsolidation.index');
    }

    public function create()
    {
        return view('budgetandanalytics.budgetworkspace.budgetconsolidation.create');
    }
}
