<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LegalexpensesController extends Controller
{
    //
    public function index()
    {
        return view('legal.reportsmanagement.legalexpenses.index');
    }

    public function create(){
        return view('legal.reportsmanagement.legalexpenses.create');
    }
}
