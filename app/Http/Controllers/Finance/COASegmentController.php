<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class COASegmentController extends Controller
{
    //
    public function index()
    {
        return view('finance.chartofaccounts.segmentconfiguration.index');
    }

    public function create()
    {
        return view('finance.chartofaccounts.segmentconfiguration.create');
    }
}
