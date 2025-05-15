<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RaiseNeedsController extends Controller
{
    //
        public function index()
    {
        return view('procurement.procurementplan.departmentneeds.raiseneed.index');
    }

    public function create(){
        return view('procurement.procurementplan.departmentneeds.raiseneed.create');
    }
}
