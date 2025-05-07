<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyNewLeaseController extends Controller
{
    //
    public function index()
    {
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.index');
    }

    public function create(){
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.create');
    }


}
