<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TopSavingAccountsController extends Controller
{
    //
   public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.topcontributors.topsavings.index');
    }

    public function create(){
        return view('budgetandanalytics.businessintelligenceandanalytics.topcontributors.topsavings.create');
    } 
}
