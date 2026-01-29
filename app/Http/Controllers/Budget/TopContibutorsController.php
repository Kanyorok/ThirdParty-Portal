<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class TopContibutorsController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.topcontributors.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.topcontributors.create');
    }
}
