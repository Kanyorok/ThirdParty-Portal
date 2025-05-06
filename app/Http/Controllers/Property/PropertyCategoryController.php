<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyCategoryController extends Controller
{
    //
    public function index()
    {
        return view('property.propertyregistry.propertycategory.index');
    }

    public function create(){
        return view('property.propertyregistry.propertycategory.create');
    }
}
