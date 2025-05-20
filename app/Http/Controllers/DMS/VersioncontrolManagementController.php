<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;

class VersioncontrolManagementController extends Controller
{
    //
    public function index()
    {
        return view('dms.versioncontrolmanagement.index');
    }

    public function create(){
        return view('dms.versioncontrolmanagement.create');
    }
}
