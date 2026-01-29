<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class DepositBookTrendsController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.depositbookgrowth.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.depositbookgrowth.create');
    }
}
