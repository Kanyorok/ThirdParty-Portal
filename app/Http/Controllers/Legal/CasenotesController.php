<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CasenotesController extends Controller
{
    //
    public function index()
    {
        return view('legal.casemanagement.casenotes.index');
    }

    public function create(){
        return view('legal.casemanagement.casenotes.create');
    }
}
