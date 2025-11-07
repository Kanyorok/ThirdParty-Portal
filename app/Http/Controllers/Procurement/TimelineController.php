<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    //
        public function index()
    {
        return view('procurement.procurementplan.procurementschedule.timeline.index');
    }

    public function create(){
        return view('procurement.procurementplan.procurementschedule.timeline.create');
    }
}
