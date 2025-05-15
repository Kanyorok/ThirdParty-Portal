<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcurementPlanMaintainController extends Controller
{
    //
    public function index()
    {
        return view('procurement.procurementplan.procurementplanmaintenance.index');
    }

    public function create(){
        return view('procurement.procurementplan.procurementplanmaintenance.create');
    }

}
