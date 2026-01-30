<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;

class DataExportToolsProductController extends Controller
{
    public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.dataexport.index');
    }

    public function create()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.dataexport.create');
    }
}
