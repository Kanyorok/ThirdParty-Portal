<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyTenantClearanceController extends Controller
{
    //
    public function index()
    {
        return view('property.tenantmanagement.tenantclearance.index');
    }

    public function create(){
        return view('property.tenantmanagement.tenantclearance.create');
    }
}
