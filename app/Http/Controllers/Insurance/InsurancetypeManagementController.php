<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InsurancetypeManagementController extends Controller
{

    //
    public function index()
    {
        return view('insurance.insurancetypemanagement.index');
    }

    public function create(){
        return view('insurance.insurancetypemanagement.create');
    }
}

