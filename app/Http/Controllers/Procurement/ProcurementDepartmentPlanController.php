<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;


class ProcurementDepartmentPlanController extends Controller
{

      //
    public function index()
    {
        return view('procurement.procurementplan.departmentalneeds.raiseneed.index');
    }

    public function create(){
        return view('procurement.procurementplan.departmentalneeds.raiseneed.create');
    }

}
