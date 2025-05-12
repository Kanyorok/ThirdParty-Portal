<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcurementReportsController extends Controller
{
    //

    public function index()
    {
        return view('procurement.reports.index');
    }

    public function create(){
        return view('procurement.reports.create');
    }
}
