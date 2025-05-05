<?php

namespace App\Http\Controllers\Fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TripManagementController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.tripmanagement.index');
    }

    public function create()
    {
        return view('fleetmanagement.tripmanagement.create');
    }
}
