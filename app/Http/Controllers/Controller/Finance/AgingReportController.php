<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgingReportController extends Controller
{
    public function index()
    {
        return view('finance.accountspayable.agingreport.index');
    }

    public function create(){
        return view('finance.accountspayable.agingreport.create');
    }
}
