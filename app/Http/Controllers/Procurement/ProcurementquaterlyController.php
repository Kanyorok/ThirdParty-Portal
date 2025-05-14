<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcurementquaterlyController extends Controller
{
    //
    public function index()
    {
        return view('procurement.procurementplan.quarterly.index');
    }

    public function create(){
        return view('procurement.procurementplan.quarterly.create');
    }
}
