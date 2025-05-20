<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class FuelManagementController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.fuelmanagement.index');
    }

    public function create()
    {
        return view('fleetmanagement.fuelmanagement.create');
    }
}
