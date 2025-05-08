<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyLeaseTerminationController extends Controller
{
    //
    public function index()
    {
        return view('property.tenantmanagement.leasemanagement.leasetermination.index');
    }

    public function create(){
        return view('property.tenantmanagement.leasemanagement.leasetermination.create');
    }

}
