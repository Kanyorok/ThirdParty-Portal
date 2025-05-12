<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReceiptsPostingController extends Controller
{
    public function index()
    {
        return view('finance.accountsreceivable.receiptsposting.index');
    }

    public function create(){
        return view('finance.accountsreceivable.receiptsposting.create');
    }
}
