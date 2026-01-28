<?php

namespace App\Http\Controllers\HRM\Payroll;

use App\Http\Controllers\Controller;

class GeneratePaySlipController extends Controller
{
    public function create()
    {
        return view("hrms.payrollmanagement.generatepayslip.create");
    }

    public function index()
    {
        return view("hrms.payrollmanagement.generatepayslip.index");
    }
}
