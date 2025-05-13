<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HearingsController extends Controller
{
    //
    public function index()
    {
        return view('legal.reportsmanagement.hearings.index');
    }

    public function create(){
        return view('legal.reportsmanagement.hearings.create');
    }
}
