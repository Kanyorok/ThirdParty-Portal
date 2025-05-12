<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderAcceptController extends Controller
{
    //
        public function index()
    {
        return view('procurement.tendering.bidopeningandevaluation.memberresponse.index');
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.memberresponse.create');
    }
}
