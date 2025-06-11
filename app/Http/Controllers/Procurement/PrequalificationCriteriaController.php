<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrequalificationCriteriaController extends Controller
{
    //
    public function index()
    {
        return view('procurement.suppliers.prequalification.criteria.index');
    }

    public function create(){
        return view('procurement.suppliers.prequalification.criteria.create');
    }

}
