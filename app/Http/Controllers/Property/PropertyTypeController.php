<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyType;
use App\Models\PropertyManagement\PropertyCategory;

class PropertyTypeController extends Controller
{
        
    public function index()
    {
        $types = PropertyType::all();
       //dd($properties);
        return view('property.propertyregistry.propertytype.index', compact('types'));
    }

    public function create(){
        $categories = PropertyCategory::all();
        return view('property.propertyregistry.propertytype.create', compact('categories'));
    }
    public function store(Request $request)
    {
        
        $request->validate([
            'PropertyTypeName'=>'required',
            'PropertyCategoryId'=>'required|string|max:50',
            'Description'=>'required|string|max:255',
        ]);
        //dd($request->all());
        PropertyType::create($request->only('PropertyTypeName', 'PropertyCategoryId', 'Description')+[
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        return redirect()->route('propertytype.index')->with('success','property type created successfully');
    }
}
