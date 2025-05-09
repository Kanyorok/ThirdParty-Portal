<?php

namespace App\Http\Controllers\Documentmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RenewalManagementController extends Controller
{
    //
    public function index()
    {
        return view('documentmanagement.renewalmanagement.index');
    }

    public function create(){
        return view('documentmanagement.renewalmanagement.create');
    }
}
