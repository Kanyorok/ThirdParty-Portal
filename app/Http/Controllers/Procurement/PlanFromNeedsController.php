<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlanFromNeedsController extends Controller
{
    //

        public function index()
    {
        return view('procurement.procurementplan.planconsolidation.loadfromneeds.index');
    }

    public function create(){
        return view('procurement.procurementplan.planconsolidation.loadfromneeds.create');
    }
}
