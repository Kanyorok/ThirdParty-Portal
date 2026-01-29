<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class DormantCASAController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.dormantaccounts.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.dormantaccounts.create');
    }
}
