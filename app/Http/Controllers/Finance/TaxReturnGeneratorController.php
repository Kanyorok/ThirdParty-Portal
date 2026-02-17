<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class TaxReturnGeneratorController extends Controller
{
    public function index()
    {
        return view('finance.taxmanagement.taxreturngenerator.index');
    }

    public function create()
    {
        return view('finance.taxmanagement.taxreturngenerator.create');
    }
}
