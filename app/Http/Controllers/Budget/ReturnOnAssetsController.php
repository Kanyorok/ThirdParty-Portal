<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReturnOnAssetsController extends Controller
{
    //
   public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.returnonassets.index');
    }

    public function create(){
        return view('budgetandanalytics.businessintelligenceandanalytics.regulatorycompliance.returnonassets.create');
    }
}
