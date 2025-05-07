<?php

namespace App\Http\Controllers\Fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehicleManagementController extends Controller
{
    public function index(){
        return view("fleetmanagement.registry.index");
    }
   
    public function create(){
        return view("fleetmanagement.registry.create");
    }
    
}
