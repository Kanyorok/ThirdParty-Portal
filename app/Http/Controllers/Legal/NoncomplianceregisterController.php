<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NoncomplianceregisterController extends Controller
{
    //
    public function index()
    {
        return view('legal.compliancemanagement.noncomplianceregister.index');
    }

    public function create(){
        return view('legal.compliancemanagement.noncomplianceregister.create');
    }
}
