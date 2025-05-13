<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CasedetailsController extends Controller
{
    //
    public function index()
    {
        return view('legal.casemanagement.casedetails.index');
    }

    public function create(){
        return view('legal.casemanagement.casedetails.create');
    }
}
