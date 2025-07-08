<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReconDashboardController extends Controller
{
    //
    public function index()
    {
        return view('finance.bankreconciliation.recondashboard.index');
    }

    public function create()
    {
        return view('finance.bankreconciliation.recondashboard.create');
    }

}
