<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderInitiationController extends Controller
{
    //
    public function index()
    {
        return view('procurement.tendering.tendersetup.tenderinitiation.index');
    }

    public function create(){
        return view('procurement.tendering.tendersetup.tenderinitiation.create');
    }
}
