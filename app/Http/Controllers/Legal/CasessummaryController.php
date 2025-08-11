<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CasessummaryController extends Controller
{
    //
    public function index()
    {
        return view('legal.reportsmanagement.casessummary.index');
    }

    public function create(){
        return view('legal.reportsmanagement.casessummary.create');
    }
}
