<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    public function index()
    {
        return view('finance.accountspayable.creditnote.index');
    }

    public function create(){
        return view('finance.accountspayable.creditnote.create');
    }
}
