<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

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
