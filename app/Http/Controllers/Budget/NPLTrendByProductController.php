<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class NPLTrendByProductController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.npltrendbyproduct.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.npltrendbyproduct.create');
    }
}
