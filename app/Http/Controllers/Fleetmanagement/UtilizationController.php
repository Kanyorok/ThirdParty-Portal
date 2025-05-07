<?php

namespace App\Http\Controllers\Fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtilizationController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.utilization.index');
    }

    public function create()
    {
        return view('fleetmanagement.utilization.create');
    }
}
