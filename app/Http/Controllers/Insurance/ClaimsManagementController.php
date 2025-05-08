<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClaimsManagementController extends Controller
{
    //
    public function index()
    {
        return view('insurance.claimsmanagement.index');
    }

    public function create(){
        return view('insurance.claimsmanagement.create');
    }
}

