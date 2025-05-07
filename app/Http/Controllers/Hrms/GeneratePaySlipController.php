<?php

namespace App\Http\Controllers\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneratePaySlipController extends Controller
{
     public function create(){
        return view("hrms.payrollmanagement.generatepayslip.create");
    }
    public function index(){
        return view("hrms.payrollmanagement.generatepayslip.index");
    }
}
