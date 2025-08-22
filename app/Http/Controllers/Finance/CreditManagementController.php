<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreditManagementController extends Controller
{
    public function index()
    {
        return view('finance.accountsreceivable.creditmanagement.index');
    }

    public function create(){
        return view('finance.accountsreceivable.creditmanagement.create');
    }

    public function show($id){
        return view('finance.accountsreceivable.creditmanagement.show');
    }
}
