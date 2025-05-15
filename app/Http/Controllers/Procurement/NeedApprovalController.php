<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NeedApprovalController extends Controller
{
    //
            public function index()
    {
        return view('procurement.procurementplan.departmentneeds.approval.index');
    }

    public function create(){
        return view('procurement.procurementplan.departmentneeds.approval.create');
    }
}

