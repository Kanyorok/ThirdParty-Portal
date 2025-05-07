<?php

namespace App\Http\Controllers\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayRollSettingsController extends Controller
{
    public function create(){
        return view("hrms.payrollmanagement.payrollsettings.create");
    }
    public function index(){
        return view("hrms.payrollmanagement.payrollsettings.index");
    }
}
