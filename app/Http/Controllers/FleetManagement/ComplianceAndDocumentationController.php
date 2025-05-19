<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;

class ComplianceAndDocumentationController extends Controller
{
    public function create()
    {
        return view("fleetmanagement.complianceanddocumentation.create");
    }

    public function index()
    {
        return view("fleetmanagement.complianceanddocumentation.index");
    }
}
