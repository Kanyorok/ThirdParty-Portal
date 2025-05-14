<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlanManualInputController extends Controller
{
    //
    public function index()
    {
        return view('procurement.procurementplan.planconsolidation.manualentry.index');
    }

    public function create(){
        return view('procurement.procurementplan.planconsolidation.manualentry.create');
    }

}
