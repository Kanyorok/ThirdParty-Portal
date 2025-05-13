<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RenewalsController extends Controller
{
    //
    public function index()
    {
        return view('legal.contractmanagement.renewals.index');
    }

    public function create(){
        return view('legal.contractmanagement.renewals.create');
    }
}
