<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrialBalanceController extends Controller
{
    //
    public function index()
    {
        return view('finance.generalledger.glreporting.trialbalance.index');
    }

    public function create(){
        return view('finance.generalledger.glreporting.trialbalance.create');
    } 
}
