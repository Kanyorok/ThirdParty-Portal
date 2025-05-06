<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyUnitController extends Controller
{
    //
    public function index()
    {
        return view('property.propertyregistry.structuralmapping.addunit.index');
    }

    public function create(){
        return view('property.propertyregistry.structuralmapping.addunit.create');
    }
}

