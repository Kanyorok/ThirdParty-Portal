<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetApprovalController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.budgetworkspace.approval.index');
    }

    public function create()
    {
        return view('budgetandanalytics.budgetworkspace.approval.create');
    }
}
