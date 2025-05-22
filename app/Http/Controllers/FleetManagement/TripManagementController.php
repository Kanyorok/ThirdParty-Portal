<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class TripManagementController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.tripmanagement.index');
    }

    public function create()
    {
        return view('fleetmanagement.tripmanagement.create');
    }
}
