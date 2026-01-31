<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;

class PropertyReportsVisualController extends Controller
{
    public function index()
    {
        return view('property.reports.visual.index');
    }

    public function create()
    {
        return view('property.reports.visual.create');
    }
}
