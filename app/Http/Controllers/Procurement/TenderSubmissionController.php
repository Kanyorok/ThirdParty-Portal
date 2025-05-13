<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderSubmissionController extends Controller
{
    //
            public function index()
    {
        return view('procurement.tendering.suppliermanagement.bidsubmission.index');
    }

    public function create(){
        return view('procurement.tendering.suppliermanagement.bidsubmission.create');
    }
}
