<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;

class InspectionController extends Controller
{
    //
    public function index()
    {
        return view('procurement.goodinspection.index');
    }

    public function create()
    {
        return view('procurement.goodinspection.create');
    }
}

