<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class LicensingController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.licensing.index');
    }

    public function create()
    {
        return view('fleetmanagement.licensing.create');
    }
}
