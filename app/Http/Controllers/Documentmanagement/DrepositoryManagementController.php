<?php

namespace App\Http\Controllers\Documentmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DrepositoryManagementController extends Controller
{
    //
    public function index()
    {
        return view('documentmanagement.drepositorymanagement.index');
    }

    public function create(){
        return view('documentmanagement.drepositorymanagement.create');
    }
}
