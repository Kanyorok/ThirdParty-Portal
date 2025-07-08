<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaxJurisdictionController extends Controller
{
    //
    public function index()
    {
        return view('finance.taxmanagement.taxjurisdictions.index');
    }

    public function create()
    {
        return view('finance.taxmanagement.taxjurisdictions.create');
    }
}

