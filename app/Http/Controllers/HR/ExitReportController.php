<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;

class ExitReportController extends Controller
{
    public function index()
    {
        return view('hr.exit.reports.index');
    }
}
