<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetLineMappingController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.budgetlinemapping.index');
    }

    public function create(){
        return view('budgetandanalytics.budgetlinemapping.create');
    }
}
