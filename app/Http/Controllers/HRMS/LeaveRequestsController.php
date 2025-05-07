<?php

namespace App\Http\Controllers\HRMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeaveRequestsController extends Controller
{
    public function index()
    {
        return view("hrms.leaverequests.index");
    }

    public function create()
    {
        return view("hrms.leaverequests.create");
    }

    
}
