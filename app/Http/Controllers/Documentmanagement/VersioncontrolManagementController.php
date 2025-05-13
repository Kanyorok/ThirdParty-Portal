<?php

namespace App\Http\Controllers\Documentmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VersioncontrolManagementController extends Controller
{
    //
    public function index()
    {
        return view('documentmanagement.versioncontrolmanagement.index');
    }

    public function create(){
        return view('documentmanagement.versioncontrolmanagement.create');
    }
}
