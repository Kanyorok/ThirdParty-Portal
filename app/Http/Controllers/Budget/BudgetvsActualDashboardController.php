<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetvsActualDashboardController extends Controller 
{
    //

   public function index()
    {
        return view('budgetandanalytics.monitoringandexecution.budgetvsactual.index');
    }

    public function create(){
        return view('budgetandanalytics.monitoringandexecution.budgetvsactual.create');
    }    
}
