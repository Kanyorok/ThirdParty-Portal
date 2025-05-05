<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddPropertyController extends Controller
{
    //
    public function index()
    {
        return view('property.registry.index');
    }

    public function create(){
        return view('property.registry.create');
    }
}
