<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CashBookController extends Controller
{
    public function index()
    {
        return view('finance.cashlink.cashbook.index');
    }

    public function create()
    {
        return view('finance.cashlink.cashbook.create');
    }
}
