<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class ServiceTrackingController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.servicetracking.index');
    }

    public function create()
    {
        return view('fleetmanagement.servicetracking.create');
    }
}
