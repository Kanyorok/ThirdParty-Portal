<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyMaintananceAssignController extends Controller
{
    //
    public function index()
    {
        return view('property.maintenanceandissues.assignrequests.index');
    }

    public function create(){
        return view('property.maintenanceandissues.assignrequests.create');
    }
}
