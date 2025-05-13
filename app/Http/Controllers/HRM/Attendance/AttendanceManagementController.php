<?php

namespace App\Http\Controllers\HRM\Attendance;

use App\Http\Controllers\Controller;

class AttendanceManagementController extends Controller
{
    public function create(){
        return view("hrms.attendancemanagement.create");
    }
    public function index(){
        return view("hrms.attendancemanagement.index");
    }

}
