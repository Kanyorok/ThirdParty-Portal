<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerMasterController extends Controller
{
    public function index()
    {
        return view('finance.accountsreceivable.customermaster.index');
    }

    public function create(){
        return view('finance.accountsreceivable.customermaster.create');
    }
}
