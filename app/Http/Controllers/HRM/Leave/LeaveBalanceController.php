<?php

namespace App\Http\Controllers\HRM\Leave;

use App\Http\Controllers\Controller;

class LeaveBalanceController extends Controller
{
    public function create()
    {
        return view("hrms.leavebalance.create");
    }

    public function index()
    {
        return view("hrms.leavebalance.index");
    }
}
