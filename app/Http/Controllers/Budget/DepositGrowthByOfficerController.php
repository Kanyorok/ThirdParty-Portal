<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class DepositGrowthByOfficerController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.depositgrowthbyofficer.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.multidimentionalanalytics.depositgrowthbyofficer.create');
    }
}
