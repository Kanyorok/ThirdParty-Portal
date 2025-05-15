<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcurementAssignMethodController extends Controller
{
    //
    public function index()
    {
        return view('procurement.procurementplan.planneditemsandactivities.assignprocurementmethod.index');
    }

    public function create(){
        return view('procurement.procurementplan.planneditemsandactivities.assignprocurementmethod.create');
    }
}
