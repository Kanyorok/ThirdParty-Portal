<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyLeaseRenewalController extends Controller
{
    //
    public function index()
    {
        return view('property.tenantmanagement.leasemanagement.leaserenewal.index');
    }

    public function create(){
        return view('property.tenantmanagement.leasemanagement.leaserenewal.create');
    }
}
