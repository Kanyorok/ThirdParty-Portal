<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountsReceivableController extends Controller
{
    public function index()
    {
        return view('finance.accountsreceivable.invoicegeneration.index');
    }

    public function create(){
        return view('finance.accountsreceivable.invoicegeneration.create');
    }
}
