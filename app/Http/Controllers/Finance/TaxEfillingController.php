<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaxEfillingController extends Controller
{
    //
    public function index()
    {
        return view('finance.taxmanagement.e-filingintegrationpanel.index');
    }

    public function create()
    {
        return view('finance.taxmanagement.e-filingintegrationpanel.create');
    }
}
