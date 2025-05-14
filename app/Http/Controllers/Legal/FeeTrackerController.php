<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeeTrackerController extends Controller
{
            public function create()
    {
        return view("legal.consultantmanagement.feetracker.create");
    }

    public function index()
    {
        return view("legal.consultantmanagement.feetracker.index");
    }
}
