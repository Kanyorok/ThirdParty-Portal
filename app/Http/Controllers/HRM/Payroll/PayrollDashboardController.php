<?php

namespace App\Http\Controllers\HRM\Payroll;

use App\Http\Controllers\Controller;

class PayrollDashboardController extends Controller
{
    public function create()
    {
        return view("hrms.payrollmanagement.payrolldashboard.create");
    }

    public function index()
    {
        return view("hrms.payrollmanagement.payrolldashboard.index");
    }
}
