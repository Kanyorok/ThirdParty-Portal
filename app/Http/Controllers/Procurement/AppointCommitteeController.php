<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppointCommitteeController extends Controller
{
    public function index()
    {
        return view('procurement.tendering.appointcommittee.index');
    }
}
