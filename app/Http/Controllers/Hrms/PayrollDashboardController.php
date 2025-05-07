<?php

namespace App\Http\Controllers\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollDashboardController extends Controller
{
    public function create(){
        return view("hrms.payrollmanagement.payrolldashboard.create");
    }
    public function index(){
        return view("hrms.payrollmanagement.payrolldashboard.index");
    }
}
