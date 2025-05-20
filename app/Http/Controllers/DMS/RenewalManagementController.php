<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;

class RenewalManagementController extends Controller
{
    //
    public function index()
    {
        return view('dms.renewalmanagement.index');
    }

    public function create(){
        return view('dms.renewalmanagement.create');
    }
}
