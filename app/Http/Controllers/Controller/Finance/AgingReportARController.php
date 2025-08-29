<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgingReportARController extends Controller
{
    public function index()
    {
        return view('finance.accountsreceivable.agingreportar.index');
    }

    public function create(){
        return view('finance.accountsreceivable.agingreportar.create');
    }
}
