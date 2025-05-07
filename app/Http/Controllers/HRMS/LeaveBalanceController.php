<?php

namespace App\Http\Controllers\HRMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
