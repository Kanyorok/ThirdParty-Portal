<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;

class TenderInitiationApproveController extends Controller
{
    public function index()
    {
        return view('procurement.tendering.tendersetup.initiationapproval.index');
    }

    public function create()
    {
        return view('procurement.tendering.tendersetup.initiationapproval.create');
    }
}
