<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProcurementApprovalController extends Controller
{
    //
    public function index()
    {
        return view('procurement.procurementplan.planapproval.approve.index');
    }

    public function create(){
        return view('procurement.procurementplan.planapproval.approve.create');
    }

}
