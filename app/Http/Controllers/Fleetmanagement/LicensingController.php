<?php

namespace App\Http\Controllers\fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LicensingController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.licensing.index');
    }

    public function create()
    {
        return view('fleetmanagement.licensing.create');
    }
}
