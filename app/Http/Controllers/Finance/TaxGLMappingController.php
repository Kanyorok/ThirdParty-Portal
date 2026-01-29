<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;

class TaxGLMappingController extends Controller
{
    public function index()
    {
        return view('finance.taxmanagement.taxglmapping.index');
    }

    public function create()
    {
        return view('finance.taxmanagement.taxglmapping.create');
    }
}
