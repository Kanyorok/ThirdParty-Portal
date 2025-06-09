<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NPLRiskController extends Controller
{
    //
   public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.loanperfomance.index');
    }

    public function create(){
        return view('budgetandanalytics.businessintelligenceandanalytics.kpidashboards.loanperfomance.create');
    }  

}
