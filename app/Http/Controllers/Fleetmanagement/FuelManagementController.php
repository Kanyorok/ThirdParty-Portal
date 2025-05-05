<?php

namespace App\Http\Controllers\fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
