<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BidScoreConsolidationController extends Controller
{
    //
  public function index()
    {
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.index');
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.scoreconsolidation.create');
    }
}
