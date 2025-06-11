<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\GenericExport;
use Maatwebsite\Excel\Facades\Excel;

class DataExportToolsProductController extends Controller
{
    //
   public function index()
    {
        return view('budgetandanalytics.businessintelligenceandanalytics.dataexport.index');
    }

    public function create(){
        return view('budgetandanalytics.businessintelligenceandanalytics.dataexport.create');
    }  

    
}
