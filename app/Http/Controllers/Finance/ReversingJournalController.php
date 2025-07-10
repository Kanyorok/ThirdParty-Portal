<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReversingJournalController extends Controller
{
    //
    public function index()
    {
        return view('finance.generalledger.reversingjournal.index');
    }

    public function create()
    {
        return view('finance.generalledger.reversingjournal.create');
    }
}
