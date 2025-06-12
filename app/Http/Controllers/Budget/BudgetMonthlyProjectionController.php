<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetMonthlyProjectionController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.budgetworkspace.monthly.index');
    }

    public function create()
    {
        return view('budgetandanalytics.budgetworkspace.monthly.create');
    }
}
