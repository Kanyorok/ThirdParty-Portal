<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyLeaseScheduleController extends Controller
{
    //
    public function index()
    {
        return view('property.tenantmanagement.leasemanagement.leaseschedule.index');
    }

    public function create(){
        return view('property.tenantmanagement.leasemanagement.leaseschedule.create');
    }

}
