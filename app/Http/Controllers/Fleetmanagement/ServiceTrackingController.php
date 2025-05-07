<?php

namespace App\Http\Controllers\fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServiceTrackingController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.servicetracking.index');
    }

    public function create()
    {
        return view('fleetmanagement.servicetracking.create');
    }
}
