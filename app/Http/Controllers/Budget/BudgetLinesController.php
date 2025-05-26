<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetLinesController extends Controller
{
    //
    //
    public function index()
    {
        return view('budgetandanalytics.budgetlinesmaster.index');
    }

    public function create(){
        return view('budgetandanalytics.budgetlinesmaster.create');
    }

}
