<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class MultidimensionalStatisticsController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.create');
    }
}
