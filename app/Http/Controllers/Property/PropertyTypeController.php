<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyTypeController extends Controller
{
    //
    public function index()
    {
        return view('property.propertyregistry.propertytype.index');
    }

    public function create(){
        return view('property.propertyregistry.propertytype.create');
    }
}
