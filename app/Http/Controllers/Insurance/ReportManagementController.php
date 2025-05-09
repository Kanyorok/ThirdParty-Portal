<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportManagementController extends Controller
{
    //
    public function index()
    {
        return view('insurance.reportmanagement.index');
    }

    public function create(){
        return view('insurance.reportmanagement.create');
    }
}
