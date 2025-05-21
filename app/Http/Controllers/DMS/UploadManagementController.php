<?php

namespace App\Http\Controllers\DMS;

use App\Http\Controllers\Controller;

class UploadManagementController extends Controller
{
    //
    public function index()
    {
        return view('dms.uploadmanagement.index');
    }

    public function create(){
        return view('dms.uploadmanagement.create');
    }
}
