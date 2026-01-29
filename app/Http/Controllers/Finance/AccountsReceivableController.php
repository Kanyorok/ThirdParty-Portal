<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class AccountsReceivableController extends Controller
{
    public function index()
    {
        return view('finance.accountsreceivable.invoicegeneration.index');
    }

    public function create()
    {
        return view('finance.accountsreceivable.invoicegeneration.create');
    }
}
