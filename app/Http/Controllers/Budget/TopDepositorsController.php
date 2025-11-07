<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TopDepositorsController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.topcontributors.topfixeddeposits.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.topcontributors.topfixeddeposits.create');
    }
}
