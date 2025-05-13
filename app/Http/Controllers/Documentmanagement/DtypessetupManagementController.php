<?php

namespace App\Http\Controllers\Documentmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DtypessetupManagementController extends Controller
{
    //
    public function index()
    {
        return view('documentmanagement.dtypessetupmanagement.index');
    }

    public function create(){
        return view('documentmanagement.dtypessetupmanagement.create');
    }
}
