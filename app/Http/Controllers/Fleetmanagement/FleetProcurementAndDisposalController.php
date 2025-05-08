<?php

namespace App\Http\Controllers\Fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FleetProcurementAndDisposalController extends Controller
{
    public function create()
    {
        return view("fleetmanagement.fleetprocurementanddisposal.create");
    }

    public function index()
    {
        return view("fleetmanagement.fleetprocurementanddisposal.index");
    }
}
