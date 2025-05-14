<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DelayedItemsController extends Controller
{
    //

        public function index()
    {
        return view('procurement.procurementplan.procurementschedule.delayeditems.index');
    }

    public function create(){
        return view('procurement.procurementplan.procurementschedule.delayeditems.create');
    }
}
