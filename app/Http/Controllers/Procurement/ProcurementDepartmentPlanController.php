<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
