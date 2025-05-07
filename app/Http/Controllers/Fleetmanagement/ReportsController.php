<?php

namespace App\Http\Controllers\Fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index()
    {
        return view('fleetmanagement.reports.index');
    }

    public function create()
    {
        return view('fleetmanagement.reports.create');
    }
}
