<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PremiumpaymentManagementController extends Controller
{
    //
    public function index()
    {
        return view('insurance.premiumpaymentmanagement.index');
    }

    public function create(){
        return view('insurance.premiumpaymentmanagement.create');
    }
}

