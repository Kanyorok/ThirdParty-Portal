<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderTypeController extends Controller
{
    //
    public function index()
    {
        return view('procurement.tendering.tendersetup.tendertype.index');
    }

    public function create(){
        return view('procurement.tendering.tendersetup.tendertype.create');
    }
}

