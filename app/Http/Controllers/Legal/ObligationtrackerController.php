<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ObligationtrackerController extends Controller
{
    //
    public function index()
    {
        return view('legal.contractmanagement.obligationtracker.index');
    }

    public function create(){
        return view('legal.contractmanagement.obligationtracker.create');
    }
}
