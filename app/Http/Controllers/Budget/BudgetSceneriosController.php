<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetSceneriosController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.scenarioplanning.index');
    }

    public function create()
    {
        return view('budgetandanalytics.scenarioplanning.create');
    }
}
