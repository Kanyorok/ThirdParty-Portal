<?php

namespace App\Http\Controllers\Documentmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccessManagementController extends Controller
{
    //
    public function index()
    {
        return view('documentmanagement.accessmanagement.index');
    }

    public function create(){
        return view('documentmanagement.accessmanagement.create');
    }
}
