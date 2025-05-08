<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyMaintenanceRequestController extends Controller
{
    //
    public function index()
    {
        return view('property.maintenanceandissues.maintenancerequest.index');
    }

    public function create(){
        return view('property.maintenanceandissues.maintenancerequest.create');
    }
}
