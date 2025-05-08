<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyPaymentFrequencyController extends Controller
{
    //
    public function index()
    {
        return view('property.tenantmanagement.leasemanagement.paymentfrequency.index');
    }

    public function create(){
        return view('property.tenantmanagement.leasemanagement.paymentfrequency.create');
    }
}
