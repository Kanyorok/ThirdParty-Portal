<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class DriverManagementController extends Controller
{
    public function create()
    {
        return view("fleetmanagement.drivermanagement.create");
    }

    public function index()
    {
        return view("fleetmanagement.drivermanagement.index");
    }
}
