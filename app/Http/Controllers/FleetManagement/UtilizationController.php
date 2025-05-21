<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class UtilizationController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.utilization.index');
    }

    public function create()
    {
        return view('fleetmanagement.utilization.create');
    }
}
