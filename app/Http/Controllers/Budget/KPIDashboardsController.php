<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KPIDashboardsController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.create');
    }

}
