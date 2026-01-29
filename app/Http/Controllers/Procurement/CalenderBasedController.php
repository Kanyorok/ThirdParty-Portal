<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;

class CalenderBasedController extends Controller
{
    public function index()
    {
        return view('procurement.procurementplan.procurementschedule.calendar-basedview.index');
    }

    public function create()
    {
        return view('procurement.procurementplan.procurementschedule.calendar-basedview.create');
    }
}
