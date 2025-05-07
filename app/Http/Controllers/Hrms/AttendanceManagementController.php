<?php

namespace App\Http\Controllers\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceManagementController extends Controller
{
    public function create(){
        return view("hrms.attendancemanagement.create");
    }
    public function index(){
        return view("hrms.attendancemanagement.index");
    }
    
}
