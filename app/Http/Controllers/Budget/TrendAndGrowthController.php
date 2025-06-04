<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrendAndGrowthController extends Controller
{
    //
   public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.trendandgrowthanalysis.index');
    }

    public function create(){
        return view('budgetandanalytics.businessintelligenceandanalytics.trendandgrowthanalysis.create');
    }  

}
