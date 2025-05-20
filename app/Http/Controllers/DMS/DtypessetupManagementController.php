<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;

class DtypessetupManagementController extends Controller
{
    //
    public function index()
    {
        return view('dms.dtypessetupmanagement.index');
    }

    public function create(){
        return view('dms.dtypessetupmanagement.create');
    }
}
