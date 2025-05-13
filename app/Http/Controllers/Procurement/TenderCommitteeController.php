<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderCommitteeController extends Controller
{
    //

        public function index()
    {
        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.index');
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.committeeappointment.create');
    }
}
