<?php

namespace App\Http\Controllers\Fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GpsController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.gps.index');
    }

    public function create()
    {
        return view('fleetmanagement.gps.create');
    }
}
