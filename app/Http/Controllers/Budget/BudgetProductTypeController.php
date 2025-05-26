<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetProductTypeController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.producttype.index');
    }

    public function create(){
        return view('budgetandanalytics.producttype.create');
    }
}
