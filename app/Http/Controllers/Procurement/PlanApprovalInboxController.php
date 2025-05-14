<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlanApprovalInboxController extends Controller
{
    //
            public function index()
    {
        return view('procurement.procurementplan.planapproval.inbox.index');
    }

    public function create(){
        return view('procurement.procurementplan.planapproval.inbox.create');
    }
}
