<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EvaluationCriteriaSetupController extends Controller
{
    //
    public function index()
    {
        return view('procurement.tendering.tendersetup.evaluationcriteriasetup.index');
    }

    public function create(){
        return view('procurement.tendering.tendersetup.evaluationcriteriasetup.create');
    }
}
