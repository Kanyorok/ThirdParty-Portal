<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class GpsController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.gps.index');
    }

    public function create()
    {
        return view('fleetmanagement.gps.create');
    }
}
