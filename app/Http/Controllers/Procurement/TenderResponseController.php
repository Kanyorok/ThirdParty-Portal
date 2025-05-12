<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TenderResponseController extends Controller 
{
    //

    public function index()
    {
        return view('procurement.tendering.suppliermanagement.InvitationResponseTracking.index');
    }

    public function create(){
        return view('procurement.tendering.suppliermanagement.InvitationResponseTracking.create');
    }
}
