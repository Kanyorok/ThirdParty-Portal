<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CoveredassetManagementController extends Controller
{
    //
    public function index()
    {
        return view('insurance.coveredassetmanagement.index');
    }

    public function create(){
        return view('insurance.coveredassetmanagement.create');
    }
}
