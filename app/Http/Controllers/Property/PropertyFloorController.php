<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyFloorController extends Controller
{
    //
    public function index()
    {
        return view('property.propertyregistry.structuralmapping.addfloor.index');
    }

    public function create(){
        return view('property.propertyregistry.structuralmapping.addfloor.create');
    }
}