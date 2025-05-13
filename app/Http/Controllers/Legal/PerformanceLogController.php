<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PerformanceLogController extends Controller
{
       public function create()
    {
        return view("legal.consultantmanagement.performancelog.create");
    }

       public function index()
    {
        return view("legal.consultantmanagement.performancelog.index");
    }
}
