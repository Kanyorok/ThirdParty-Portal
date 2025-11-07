<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BusinessAnalyticsDashboardController extends Controller
{

    //
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.bianalyticsanddashboard.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.bianalyticsanddashboard.create');
    }

}
