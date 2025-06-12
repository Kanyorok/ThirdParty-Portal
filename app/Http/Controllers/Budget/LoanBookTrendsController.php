<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoanBookTrendsController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.loanbookgrowth.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.loanbookgrowth.create');
    }
}
