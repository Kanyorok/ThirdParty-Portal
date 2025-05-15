<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubmitForApprovalController extends Controller
{
    //
        public function index()
    {
        return view('procurement.procurementplan.planapproval.submitforapproval.index');
    }

    public function create(){
        return view('procurement.procurementplan.planapproval.submitforapproval.create');
    }
}
