<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderclarificationController extends Controller
{
    //

        public function index()
    {
        return view('procurement.tendering.suppliermanagement.clarificationhandling.index');
    }

    public function create(){
        return view('procurement.tendering.suppliermanagement.clarificationhandling.create');
    }
}

