<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegulatorychecklistController extends Controller
{
    //
    public function index()
    {
        return view('legal.compliancemanagement.regulatorychecklist.index');
    }

    public function create(){
        return view('legal.compliancemanagement.regulatorychecklist.create');
    }
}
