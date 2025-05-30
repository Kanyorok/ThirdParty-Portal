<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetProductEntryController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.budgetworkspace.entry.index');
    }

    public function create()
    {
        return view('budgetandanalytics.budgetworkspace.entry.create');
    }
}
