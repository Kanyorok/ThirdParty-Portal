<?php

namespace App\Http\Controllers\Documentmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrailManagementController extends Controller
{
    //
    public function index()
    {
        return view('documentmanagement.trailmanagement.index');
    }

    public function create(){
        return view('documentmanagement.trailmanagement.create');
    }
}

