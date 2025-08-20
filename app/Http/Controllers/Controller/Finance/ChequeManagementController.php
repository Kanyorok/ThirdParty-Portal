<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChequeManagementController extends Controller
{
    public function index()
    {
        return view('finance.cashlink.chequemanagement.index');
    }

    public function create()
    {
        return view('finance.cashlink.chequemanagement.create');
    }
}
