<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrequalificationApplicationsController extends Controller
{
    //
    public function index()
    {
        return view('procurement.suppliers.prequalification.applications.index');
    }

    public function create(){
        return view('procurement.suppliers.prequalification.applications.create');
    }

        public function show(){
        return view('procurement.suppliers.prequalification.applications.show');
    }

}
