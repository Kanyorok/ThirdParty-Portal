<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractapprovalController extends Controller
{
    //
    public function index()
    {
        return view('legal.contractmanagement.contractapproval.index');
    }

    public function create(){
        return view('legal.contractmanagement.contractapproval.create');
    }
}
