<?php

namespace App\Http\Controllers\HRMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeManagementController extends Controller
{
    public function index()
    {
        return view('hrms.employeemanagement.index');
    }

    public function create()
    {
        return view('hrms.employeemanagement.create');
    }
}
