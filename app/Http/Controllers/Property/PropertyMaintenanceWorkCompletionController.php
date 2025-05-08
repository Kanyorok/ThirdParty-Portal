<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyMaintenanceWorkCompletionController extends Controller
{
    //
    public function index()
    {
        return view('property.maintenanceandissues.workcompletion.index');
    }

    public function create(){
        return view('property.maintenanceandissues.workcompletion.create');
    }
}
