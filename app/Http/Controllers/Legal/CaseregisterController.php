<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CaseregisterController extends Controller
{
    //
    public function index()
    {
        return view('legal.casemanagement.caseregister.index');
    }

    public function create(){
        return view('legal.casemanagement.caseregister.create');
    }
}
