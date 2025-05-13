<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompliancestatusController extends Controller
{
            public function create()
    {
        return view("legal.reportsmanagement.compliancestatus.create");
    }

    public function index()
    {
        return view("legal.reportsmanagement.compliancestatus.index");
    }
}
