<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;


class ProcurementDepartmentPlanController extends Controller
{

    //
    public function index()
    {
        $this->authorize('viewAny', \App\Models\Procurement\DepartmentNeed::class);
        return view('procurement.procurementplan.departmentalneeds.raiseneed.index');
    }

    public function create()
    {
        $this->authorize('create', \App\Models\Procurement\DepartmentNeed::class);
        return view('procurement.procurementplan.departmentalneeds.raiseneed.create');
    }
}
