<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class TaxEfillingController extends Controller
{
    public function index()
    {
        return view('finance.taxmanagement.e-filingintegrationpanel.index');
    }

    public function create()
    {
        return view('finance.taxmanagement.e-filingintegrationpanel.create');
    }
}
