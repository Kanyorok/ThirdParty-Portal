<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetPeriodController extends Controller
{
    //
        public function index()
    {
        return view('budgetandanalytics.budgetperiod.index');
    }

    public function create(){
        return view('budgetandanalytics.budgetperiod.create');
    }
}
