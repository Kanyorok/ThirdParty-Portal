<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractdraftingController extends Controller
{
    //
    public function index()
    {
        return view('legal.contractmanagement.contractdrafting.index');
    }

    public function create(){
        return view('legal.contractmanagement.contractdrafting.create');
    }
}
