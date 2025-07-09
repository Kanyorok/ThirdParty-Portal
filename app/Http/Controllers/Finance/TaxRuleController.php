<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaxRuleController extends Controller
{
    //
    public function index()
    {
        return view('finance.taxmanagement.taxruleconfiguration.index');
    }

    public function create()
    {
        return view('finance.taxmanagement.taxruleconfiguration.create');
    }
}
