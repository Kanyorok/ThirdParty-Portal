<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;

class DrepositoryManagementController extends Controller
{
    //
    public function index()
    {
        return view('dms.drepositorymanagement.index');
    }

    public function create(){
        return view('dms.drepositorymanagement.create');
    }
}
