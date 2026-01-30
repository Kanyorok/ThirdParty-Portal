<?php

namespace App\Http\Controllers\HRM\Leave;

use App\Http\Controllers\Controller;

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
