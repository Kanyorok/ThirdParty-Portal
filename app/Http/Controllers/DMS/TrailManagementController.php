<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;

class TrailManagementController extends Controller
{
    //
    public function index()
    {
        return view('dms.trailmanagement.index');
    }

    public function create(){
        return view('dms.trailmanagement.create');
    }
}

