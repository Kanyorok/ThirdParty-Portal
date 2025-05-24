<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetTopDownAllocationController extends Controller
{
    //
   public function index()
    {
        return view('budgetandanalytics.budgetworkspace.topdownallocationtool.index');
    }

    public function create(){
        return view('budgetandanalytics.budgetworkspace.topdownallocationtool.create');
    }

}
