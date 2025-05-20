<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;

class AccessManagementController extends Controller
{
    //
    public function index()
    {
        return view('dms.accessmanagement.index');
    }

    public function create(){
        return view('dms.accessmanagement.create');
    }
}
