<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RentDashboardController extends Controller
{
    //
    public function index()
    {
        return view('property.billingandreceipting.rentdashboard.index');
    }

    public function create(){
        return view('property.billingandreceipting.rentdashboard.create');
    }
}
