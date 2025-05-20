<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class VehicleManagementController extends Controller
{
    public function index(){
        return view("fleetmanagement.registry.index");
    }

    public function create(){
        return view("fleetmanagement.registry.create");
    }

}
