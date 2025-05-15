<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlanEditController extends Controller
{
    //
    public function index()
    {
        return view('procurement.procurementplan.planapproval.ammendplan.index');
    }

    public function create(){
        return view('procurement.procurementplan.planapproval.ammendplan.create');
    }
}

