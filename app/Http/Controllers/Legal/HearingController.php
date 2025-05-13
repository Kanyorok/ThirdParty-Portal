<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HearingController extends Controller
{
    //
    public function index()
    {
        return view('legal.casemanagement.hearing.index');
    }

    public function create(){
        return view('legal.casemanagement.hearing.create');
    }
}
