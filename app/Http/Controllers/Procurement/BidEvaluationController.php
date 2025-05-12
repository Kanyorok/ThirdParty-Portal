<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BidEvaluationController extends Controller
{
    //

        public function index()
    {
        return view('procurement.tendering.bidopeningandevaluation.evaluation.index');
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.evaluation.create');
    }

}
