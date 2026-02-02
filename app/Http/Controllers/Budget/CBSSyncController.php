<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class CBSSyncController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.settings.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.settings.create');
    }
}
