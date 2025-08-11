<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class JournalEntryController extends Controller
{
    //
    public function index()
    {
        return view('finance.generalledger.journalentry.index');
    }

    public function create()
    {
        return view('finance.generalledger.journalentry.create');
    }

}
