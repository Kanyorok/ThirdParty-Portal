<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BankAccountSetupController extends Controller
{
    public function index()
    {
        return view('finance.cashlink.bankaccountsetup.index');
    }

    public function create()
    {
        return view('finance.cashlink.bankaccountsetup.create');
    }
}
