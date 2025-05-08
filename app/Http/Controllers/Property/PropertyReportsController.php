<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyReportsController extends Controller
{
    //
    public function index()
    {
        return view('property.reports.reports.index');
    }

    public function create(){
        return view('property.reports.reports.create');
    }
}
