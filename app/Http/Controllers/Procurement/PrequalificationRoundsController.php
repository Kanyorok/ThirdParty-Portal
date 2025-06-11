<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrequalificationRoundsController extends Controller
{
    //
    public function index()
    {
        return view('procurement.suppliers.prequalification.roundmanagement.index');
    }

    public function create()
    {
        return view('procurement.suppliers.prequalification.roundmanagement.create');
    }

}
