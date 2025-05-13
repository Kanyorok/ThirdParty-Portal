<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EvaluatorDashboardController extends Controller
{
    //

     public function index()
    {
        return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.index');
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.evaluationdashboard.create');
    }
}
