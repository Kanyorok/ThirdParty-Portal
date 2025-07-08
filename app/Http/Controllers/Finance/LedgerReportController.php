<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LedgerReportController extends Controller
{
    //
    public function index()
    {
        return view('finance.generalledger.glreporting.ledgerreport.index');
    }

    public function create(){
        return view('finance.generalledger.glreporting.ledgerreport.create');
    } 
}
