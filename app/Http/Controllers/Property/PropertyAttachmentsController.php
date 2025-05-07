<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PropertyAttachmentsController extends Controller
{
    //
    public function index()
    {
        return view('property.propertyregistry.propertyattachments.index');
    }

    public function create(){
        return view('property.propertyregistry.propertyattachments.create');
    }
}
