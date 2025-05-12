<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderOpeningController extends Controller
{
    //
    public function index()
    {
        return view('procurement.tendering.bidopeningandevaluation.opening.index');
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.opening.create');
    }
}
