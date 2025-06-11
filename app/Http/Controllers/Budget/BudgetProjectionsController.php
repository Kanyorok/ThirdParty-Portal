<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetProjectionsController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.budgetprojections.index');
    }

    public function create(){
        return view('budgetandanalytics.budgetprojections.create');
    }
}
