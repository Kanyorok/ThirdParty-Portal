<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcurementPlanDashboardController extends Controller
{
    //

        public function index()
    {
        return view('procurement.procurementplan.planneditemsandactivities.viewplanneditems.index');
    }

    public function create(){
        return view('procurement.procurementplan.planneditemsandactivities.viewplanneditems.create');
    }
}

