<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetProductMasterController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.productmaster.index');
    }

    public function create()
    {
        return view('budgetandanalytics.productmaster.create');
    }
}
