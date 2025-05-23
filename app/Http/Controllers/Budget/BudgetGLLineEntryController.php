<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetGLLineEntryController extends Controller
{
    //
   public function index()
    {
        return view('budgetandanalytics.budgetworkspace.entrybyglline.index');
    }

    public function create(){
        return view('budgetandanalytics.budgetworkspace.entrybyglline.create');
    }
}

