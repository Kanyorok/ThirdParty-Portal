<?php

namespace App\Http\Controllers\Fleetmanagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
