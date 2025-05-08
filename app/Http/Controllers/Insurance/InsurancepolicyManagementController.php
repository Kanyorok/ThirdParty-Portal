<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InsurancepolicyManagementController extends Controller
{
    //
    public function index()
    {
        return view('insurance.insurancepolicymanagement.index');
    }

    public function create(){
        return view('insurance.insurancepolicymanagement.create');
    }
}
