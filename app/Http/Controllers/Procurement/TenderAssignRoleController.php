<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderAssignRoleController extends Controller
{
    //
     public function index()
    {
        return view('procurement.tendering.bidopeningandevaluation.committeeroles.index');
    }

    public function create(){
        return view('procurement.tendering.bidopeningandevaluation.committeeroles.create');
    }
}
