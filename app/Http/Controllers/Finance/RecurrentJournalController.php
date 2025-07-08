<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecurrentJournalController extends Controller
{
    //
    public function index()
    {
        return view('finance.generalledger.recurrentjournal.index');
    }

    public function create(){
        return view('finance.generalledger.recurrentjournal.create');
    } 
}
