<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CasedocumentsController extends Controller
{
    //
    public function index()
    {
        return view('legal.casemanagement.casedocuments.index');
    }

    public function create(){
        return view('legal.casemanagement.casedocuments.create');
    }
}
