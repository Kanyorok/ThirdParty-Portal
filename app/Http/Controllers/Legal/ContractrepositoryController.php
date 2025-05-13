<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContractrepositoryController extends Controller
{
    //
    public function index()
    {
        return view('legal.contractmanagement.contractrepository.index');
    }

    public function create(){
        return view('legal.contractmanagement.contractrepository.create');
    }
}
