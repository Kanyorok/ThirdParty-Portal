<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyBlockController extends Controller
{
    //
    public function index()
    {
        return view('property.propertyregistry.structuralmapping.addblock.index');
    }

    public function create(){
        return view('property.propertyregistry.structuralmapping.addblock.create');
    }
}
