<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetDriversController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.budgetdrivers.index');
    }

    public function create(){
        return view('budgetandanalytics.budgetdrivers.create');
    }
}
