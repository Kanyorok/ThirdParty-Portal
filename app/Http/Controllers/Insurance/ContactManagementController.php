<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactManagementController extends Controller
{
    //
    public function index()
    {
        return view('insurance.contactmanagement.index');
    }

    public function create(){
        return view('insurance.contactmanagement.create');
    }
}
