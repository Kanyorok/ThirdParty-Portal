<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProviderManagementController extends Controller
{
    //
    public function index()
    {
        return view('insurance.providermanagement.index');
    }

    public function create(){
        return view('insurance.providermanagement.create');
    }
}


