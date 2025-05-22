<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;

class SearchManagementController extends Controller
{
    //
    public function index()
    {
        return view('dms.searchmanagement.index');
    }

    public function create(){
        return view('dms.searchmanagement.create');
    }
}
