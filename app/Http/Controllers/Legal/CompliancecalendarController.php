<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompliancecalendarController extends Controller
{
    //
    public function index()
    {
        return view('legal.compliancemanagement.compliancecalendar.index');
    }

    public function create(){
        return view('legal.compliancemanagement.compliancecalendar.create');
    }
}
