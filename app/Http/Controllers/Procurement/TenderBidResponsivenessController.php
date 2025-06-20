<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderBidResponsivenessController extends Controller
{
    //   
    
    public function index()
    {
        return view('procurement.tendering.bidopeningandevaluation.responsivenesscheck.index');
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.responsivenesscheck.create');
    }

    public function show(){
        
    }
}
