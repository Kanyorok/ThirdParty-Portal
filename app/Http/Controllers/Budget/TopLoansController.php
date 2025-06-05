<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TopLoansController extends Controller
{
    //
   public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.topcontributors.toploans.index');
    }

    public function create(){
        return view('budgetandanalytics.businessintelligenceandanalytics.topcontributors.toploans.create');
    }  
}
