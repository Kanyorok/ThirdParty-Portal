<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConsolidatedDashboardController extends Controller
{
    //
    public function index()
    {
        return view('procurement.procurementplan.planconsolidation.dashboard.index');
    }

    public function create(){
        return view('procurement.procurementplan.planconsolidation.dashboard.create');
    }
}
