<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcurementPlanDetailController extends Controller
{
    //
    public function index()
    {
        return view('procurement.procurementplan.plandetail.index');
    }

    public function create(){
        return view('procurement.procurementplan.plandetail.create');
    }
}
