<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlanvsActualController extends Controller
{
    //
        public function index()
    {
        return view('procurement.procurementplan.plansvsactual.index');
    }

    public function create(){
        return view('procurement.procurementplan.plansvsactual.create');
    }
}
