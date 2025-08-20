<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerStatementController extends Controller
{
    public function index()
    {
        return view('finance.accountsreceivable.customerstatement.index');
    }

    public function create(){
        return view('finance.accountsreceivable.customerstatement.create');
    }
}
