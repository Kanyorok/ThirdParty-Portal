<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FillingtrackerController extends Controller
{
    //
    public function index()
    {
        return view('legal.compliancemanagement.fillingtracker.index');
    }

    public function create(){
        return view('legal.compliancemanagement.fillingtracker.create');
    }
}
