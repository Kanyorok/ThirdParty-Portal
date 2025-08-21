<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BalanceSheetController extends Controller
{
    public function index()
    {
        return view('finance.financialreporting.balancesheet.index');
    }

    public function create()
    {
        return view('finance.financialreporting.balancesheet.create');
    }
}
