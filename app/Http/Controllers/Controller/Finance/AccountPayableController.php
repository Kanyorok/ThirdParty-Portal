<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountPayableController extends Controller
{
    public function index()
    {
        return view('finance.accountspayable.invoiceentry.index');
    }

    public function create(){
        return view('finance.accountspayable.invoiceentry.create');
    }
}
