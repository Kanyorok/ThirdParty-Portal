<?php

namespace App\Http\Controllers\Documentmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchManagementController extends Controller
{
    //
    public function index()
    {
        return view('documentmanagement.searchmanagement.index');
    }

    public function create(){
        return view('documentmanagement.searchmanagement.create');
    }
}
