<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.reports.index');
    }

    public function create()
    {
        return view('fleetmanagement.reports.create');
    }
}
