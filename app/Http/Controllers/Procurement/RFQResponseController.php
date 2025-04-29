<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RFQResponseController extends Controller
{
    public function create()
    {
       return view('procurement.rfqresponse.create');
    }
}
