<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RenewalManagementController extends Controller
{
    //
    public function index()
    {
        return view('insurance.renewalmanagement.index');
    }

    public function create(){
        return view('insurance.renewalmanagement.create');
    }
}
