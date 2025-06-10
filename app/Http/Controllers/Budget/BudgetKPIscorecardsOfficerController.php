<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BudgetKPIscorecardsOfficerController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.monitoringandexecution.kpiscorecards_officer.index');
    }

    public function create()
    {
        return view('budgetandanalytics.monitoringandexecution.kpiscorecards.kpiscorecards_officer.create');
    }

}
