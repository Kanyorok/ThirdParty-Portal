<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CashManagementController extends Controller
{
    public function index()
    {
        return view('finance.cashlink.cashmanagement.index');
    }

    public function create()
    {
        return view('finance.cashlink.cashmanagement.create');
    }
}
