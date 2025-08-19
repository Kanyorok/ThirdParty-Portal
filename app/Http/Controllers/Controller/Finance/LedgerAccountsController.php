<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LedgerAccountsController extends Controller
{
    public function index()
    {
        return view('finance.ledgeraccounts.index');
    }

    public function create()
    {
        return view('finance.ledgeraccounts.create');
    }
}