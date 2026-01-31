<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;

class ProcurementReportsController extends Controller
{
    public function index()
    {
        return view('procurement.reports.index');
    }

    public function create()
    {
        return view('procurement.reports.create');
    }
}
