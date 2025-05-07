<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyNewTenantController extends Controller
{
    //
    public function index()
    {
        return view('property.tenantmanagement.tenantmaintenance.index');
    }

    public function create(){
        return view('property.tenantmanagement.tenantmaintenance.create');
    }

    public function edit(){
        return view('property.tenantmanagement.tenantmaintenance.edit');
    }
}
