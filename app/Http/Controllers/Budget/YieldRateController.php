<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class YieldRateController extends Controller
{
    //
    public function index()
    {
        return view('budgetandanalytics.yieldexpenserate.index');
    }

    public function create(){
        return view('budgetandanalytics.yieldexpenserate.create');
    }

}
