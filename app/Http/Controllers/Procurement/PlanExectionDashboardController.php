<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;

class PlanExectionDashboardController extends Controller
{
    public function index()
    {
        return view('procurement.procurementplan.planexecution.executiondashboard.index');
    }

    public function create()
    {
        return view('procurement.procurementplan.planexecution.executiondashboard.create');
    }
}
