<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetActivitiesController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.budgetactivities.index');
    }

    public function create()
    {
        return view('budgetandanalytics.budgetactivities.create');
    }

}
