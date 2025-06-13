<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrequalificationEvaluationController extends Controller
{
    //
    public function index()
    {
        return view('procurement.suppliers.prequalification.evaluation.index');
    }

    public function create()
    {
        return view('procurement.suppliers.prequalification.evaluation.create');
    }
}
